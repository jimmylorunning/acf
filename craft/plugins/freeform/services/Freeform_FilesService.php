<?php
/**
 * Freeform for Craft
 *
 * @package       Solspace:Freeform
 * @author        Solspace, Inc.
 * @copyright     Copyright (c) 2008-2018, Solspace, Inc.
 * @link          https://solspace.com/craft/freeform
 * @license       https://solspace.com/software/license-agreement
 */

namespace Craft;

use Solspace\Freeform\Library\Composer\Components\AbstractField;
use Solspace\Freeform\Library\Composer\Components\FieldInterface;
use Solspace\Freeform\Library\Composer\Components\Fields\FileUploadField;
use Solspace\Freeform\Library\Composer\Components\Form;
use Solspace\Freeform\Library\Exceptions\FreeformException;
use Solspace\Freeform\Library\FileUploads\FileUploadHandlerInterface;
use Solspace\Freeform\Library\FileUploads\FileUploadResponse;

class Freeform_FilesService extends BaseApplicationComponent implements FileUploadHandlerInterface
{
    /** @var array */
    private static $fileUploadFieldIds;

    /**
     * Uploads a file and flags it as "unfinalized"
     * It will be finalized only after the form has been submitted fully
     * All unfinalized files will be deleted after a certain amount of time
     *
     * @param FileUploadField $field
     * @param Form            $form
     *
     * @return FileUploadResponse|null
     */
    public function uploadFile(FileUploadField $field, Form $form)
    {
        if (!$field->getAssetSourceId()) {
            return null;
        }

        $assetService = craft()->assets;
        $folder       = $assetService->getRootFolderBySourceId($field->getAssetSourceId());

        $uploadedFileCount = count($_FILES[$field->getHandle()]['name']);

        if (!$folder) {
            return null;
        }

        if (!$this->onBeforeUpload($field, $form)->performAction) {
            return null;
        }

        $uploadedAssetIds = $errors = [];
        for ($i = 0; $i < $uploadedFileCount; $i++) {
            $uploadedFile = UploadedFile::getInstanceByName($field->getHandle() . "[$i]");

            if (!$uploadedFile || !$uploadedFile->name) {
                continue;
            }

            $response = null;
            try {
                $response = $assetService->insertFileByLocalPath(
                    $uploadedFile->tempName,
                    $uploadedFile->name,
                    $folder->id,
                    AssetConflictResolution::KeepBoth
                );
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
                return new FileUploadResponse(null, [$e->getMessage()]);
            }

            if ($response->isSuccess()) {
                $assetId = $response->getDataItem('fileId');
                $this->markAssetUnfinalized($assetId);

                $this->onAfterUpload($field, $assetId, $form);

                $uploadedAssetIds[] = $assetId;
            }

            if ($response->isError()) {
                $errors = array_merge($errors, $response->getErrors());
            }
        }

        if ($uploadedAssetIds) {
            return new FileUploadResponse($uploadedAssetIds);
        }

        return new FileUploadResponse(null, $errors);
    }

    /**
     * Returns an array of all fields which are of type FILE
     *
     * @return array
     */
    public function getFileUploadFieldIds()
    {
        if (null === self::$fileUploadFieldIds) {
            $fileUploadFieldIds = craft()
                ->db
                ->createCommand()
                ->select('id')
                ->from('freeform_fields')
                ->where("type = '" . FieldInterface::TYPE_FILE . "'")
                ->queryColumn();

            self::$fileUploadFieldIds = $fileUploadFieldIds;
        }

        return self::$fileUploadFieldIds;
    }

    /**
     * Stores the unfinalized assetId in the database
     * So that it can be deleted later if the form hasn't been finalized
     *
     * @param int $assetId
     */
    public function markAssetUnfinalized($assetId)
    {
        $record          = new Freeform_UnfinalizedFileRecord();
        $record->assetId = $assetId;
        $record->save(false);
    }

    /**
     * Remove all unfinalized assets which are older than the TTL
     * specified in settings
     */
    public function cleanUpUnfinalizedAssets()
    {
        $date = new \DateTime('-180 minutes');

        $assetIds = craft()
            ->db
            ->createCommand()
            ->select('assetId')
            ->from('freeform_unfinalized_files')
            ->where(
                'dateCreated < :now',
                ['now' => $date->format('Y-m-d H:i:s')]
            )
            ->queryColumn();

        if (!empty($assetIds)) {
            foreach ($assetIds as $assetId) {
                try {
                    if (craft()->assets->getFileById($assetId)) {
                        craft()->assets->deleteFiles($assetId);
                    }
                } catch (\Exception $e) {
                    if (craft()->userSession->isAdmin()) {
                        craft()->userSession->setError($e->getMessage());
                    }
                }

                craft()->db->createCommand()->delete(
                    'freeform_unfinalized_files',
                    'assetId = :assetId',
                    ['assetId' => $assetId]
                );
            }
        }
    }

    /**
     * Get a serializable array of asset sources containing
     * their ID, name and type
     *
     * @return array
     */
    public function getAssetSources()
    {
        $assetSourceModels = craft()->assetSources->getAllSources(false);
        $assetSources      = [];
        foreach ($assetSourceModels as $source) {
            $assetSources[] = [
                'id'   => (int)$source->id,
                'name' => $source->name,
                'type' => $source->type,
            ];
        }

        return $assetSources;
    }

    /**
     * Get a key-value list of asset sources, indexed by ID
     *
     * @return array
     */
    public function getAssetSourceList()
    {
        $assetSourceModels = craft()->assetSources->getAllSources(false);
        $assetSources      = [];
        foreach ($assetSourceModels as $source) {
            $assetSources[(int)$source->id] = $source->name;
        }

        return $assetSources;
    }

    /**
     * Returns an array of all file kinds
     * [type => [ext, ext, ..]
     * I.e. ["image" => ["gif", "png", "jpg", "jpeg", ..]]
     *
     * @return array
     */
    public function getFileKinds()
    {
        $fileKinds = IOHelper::getFileKinds();
        $fileKinds['illustrator']['extensions'][] = 'eps';

        $returnArray = [];
        foreach ($fileKinds as $kind => $extensions) {
            $returnArray[$kind] = $extensions['extensions'];
        }

        return $returnArray;
    }

    /**
     * @param FileUploadField $field
     * @param Form            $form
     *
     * @return Event
     * @throws \CException
     */
    private function onBeforeUpload(FileUploadField $field, Form $form)
    {
        $event = new Event($this, ['field' => $field, 'form' => $form]);
        $this->raiseEvent(FreeformPlugin::EVENT_BEFORE_UPLOAD, $event);

        return $event;
    }

    /**
     * @param FileUploadField $field
     * @param int             $assetId
     * @param Form            $form
     *
     * @return Event
     * @throws \CException
     */
    private function onAfterUpload(FileUploadField $field, $assetId, Form $form)
    {
        $event = new Event($this, ['field' => $field, 'assetId' => $assetId, 'form' => $form]);
        $this->raiseEvent(FreeformPlugin::EVENT_AFTER_UPLOAD, $event);

        return $event;
    }
}
