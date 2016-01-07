<?php
namespace Craft;


class FormBuilder2_EntryService extends BaseApplicationComponent
{
  
  private $_entriesById;
  private $_allEntryIds;
  private $_fetchedAllEntries = false;

  /**
   * Fires 'onBeforeSave' Form Entry
   *
   */
  public function onBeforeSave(Event $event)
  {
    $this->raiseEvent('onBeforeSave', $event);
  }

  /**
   * Get All Entry ID's
   *
   */
  public function getAllEntryIds()
  {
    if (!isset($this->_allEntryIds)) {
      if ($this->_fetchedAllEntries) {
        $this->_allEntryIds = array_keys($this->_entriesById);
      } else {
        $this->_allEntryIds = craft()->db->createCommand()
          ->select('id')
          ->from('formbuilder2_entries')
          ->queryColumn();
      }
    }
    return $this->_allEntryIds;
  }

  /**
   * Get All Entries
   *
   */
  public function getAllEntries()
  {
    $entries = FormBuilder2_EntryRecord::model()->findAll();
    return $entries;
  }

  /**
   * Get Total Entries Count
   *
   */
  public function getTotalEntries()
  {
    return count($this->getAllEntryIds());
  }

  /**
   * Get Form By Handle
   *
   */
  public function getFormByHandle($handle)
  {
    $formRecord = FormBuilder2_FormRecord::model()->findByAttributes(array(
      'handle' => $handle,
    ));

    if (!$formRecord) { return false; }
    return FormBuilder2_FormModel::populateModel($formRecord);
  }

  /**
   * Get Form Entry By Id
   *
   */
  // public function getFormEntryById($id)
  // {
  //   return craft()->elements->getElementById($id, 'FormBuilder2');
  // }

  /**
   * Get Submission By ID
   *
   */
  public function getSubmissionById($entryId)
  {
    return FormBuilder2_EntryRecord::model()->findById($entryId);
  }

  /**
   * Get All Entries From Form ID
   *
   */
  public function getAllEntriesFromFormID($formId)
  {
    $result = craft()->db->createCommand()
      ->select('*')
      ->from('formbuilder2_entries')
      ->where('formId = :formId', array(':formId' => $formId))
      ->queryAll();
    return $result;
  }

  /**
   * Validate values of a submitted form
   *
   */
  public function validateEntry($form, $submissionData) {
    $fieldLayoutFields = $form->getFieldLayout()->getFields();
    $errorMessage = [];

    foreach ($fieldLayoutFields as $key => $fieldLayoutField) {
      $requiredField = $fieldLayoutField->attributes['required'];
      $fieldId = $fieldLayoutField->fieldId;
      $field = craft()->fields->getFieldById($fieldId);

      $userValue = (array_key_exists($field->handle, $submissionData)) ? $submissionData[$field->handle] : false;          

      if ($requiredField == 1) {
        $field->required = true;
      }
      
      switch ($field->type) {
        case "PlainText":
          if ($field->required) {
            $text = craft()->request->getPost($field->handle);
            if ($text == '') {
              $errorMessage[] = $field->name . ' cannot be blank.';
            }
          }
        break;
        case "RichField":
          if ($field->required) {
            $richField = craft()->request->getPost($field->handle);
            if ($richField == '') {
              $errorMessage[] = $field->name . ' cannot be blank.';
            }
          }
        break;
        case "Number":
          $number = craft()->request->getPost($field->handle);
          if ($field->required) {
            if (!ctype_digit($number)) {
              $errorMessage[] = $field->name . ' cannot be blank and needs to contain only numbers.';
            }
          } else {
            if (!ctype_digit($number) && (!empty($number))) {
              $errorMessage[] = $field->name . ' needs to contain only numbers.';
            }
          }
        break;
        case "MultiSelect":
          $multiselect = craft()->request->getPost($field->handle);
          if ($field->required) {
            if ($multiselect == '') {
              $errorMessage[] = $field->name . ' please select at least one.';
            }
          }
        break;
        case "RadioButtons":
          $radiobuttons = craft()->request->getPost($field->handle);
          if ($field->required) {
            if ($radiobuttons == '') {
              $errorMessage[] = $field->name . ' please select one.';
            }
          }
        break;
        case "Dropdown":
          $dropdown = craft()->request->getPost($field->handle);
          if ($field->required) {
            if ($dropdown == '') {
              $errorMessage[] = $field->name . ' please select one.';
            }
          }
        break;
        case "Checkboxes":
          $checkbox = craft()->request->getPost($field->handle);
          if ($field->required) {
            if (count($checkbox) == 1) {
              $errorMessage[] = $field->name . ' please select at least one.';
            }
          }
        break;
      }
    }
    return $errorMessage;
  }

  /**
   * Process Submission Entry
   *
   */
  public function processSubmissionEntry(FormBuilder2_EntryModel $submission)
  { 
    // Fire Before Save Event
    $this->onBeforeSave(new Event($this, array(
      'entry' => $submission
    )));

    $form                       = craft()->formBuilder2_form->getFormById($submission->formId);
    $formFields                 = $form->fieldLayout->getFieldLayout()->getFields();
    $attributes                 = $form->getAttributes();
    $formSettings               = $attributes['formSettings'];

    $submissionRecord = new FormBuilder2_EntryRecord();

    // File Uploads
    if ($submission->files) {
      $fileIds = [];
      foreach ($submission->files as $key => $value) {
        if ($value->size) {
          $folder = $value->getFolder();
          
          // Make sure folder excist
          $source = $folder->getSource()['settings'];
          IOHelper::ensureFolderExists($source['path'], $suppressErrors = true);

          // Save/Store Files
          $fileName = IOHelper::getFileName($value->filename, true);
          $response = craft()->assets->insertFileByLocalPath($value->originalName, $fileName, $value->folderId, AssetConflictResolution::KeepBoth);
          $fileIds[] = $response->getDataItem('fileId');

          // Delete Temp Files
          IOHelper::deleteFile($value->originalName, true);

          if ($response->isError()) {
            $response->setError(Craft::t('There was an error with file uploads.'));
          }
        }
        $submissionRecord->files = $fileIds;
      }
    }
    
    // Build Entry Record
    $submissionRecord->formId       = $submission->formId;
    $submissionRecord->title        = $submission->title;
    $submissionRecord->submission   = $submission->submission;

    $submissionRecord->validate();
    $submission->addErrors($submissionRecord->getErrors());

    // Save To Database
    if (!$submission->hasErrors()) {
      $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
      try {
        if (craft()->elements->saveElement($submission)) {
          $submissionRecord->id = $submission->id;
          $submissionRecord->save(false);

          if ($transaction !== null) { 
            $transaction->commit(); 
          }
          return $submissionRecord->id;
        } else { 
          return false; 
        }
      } catch (\Exception $e) {
        if ($transaction !== null) { 
          $transaction->rollback(); 
        }
        throw $e;
      }
      return true;
    } else { 
      return false; 
    }
  }

  /**
   * Send Email Notification
   *
   */
  public function sendEmailNotification($form, $postUploads, $message, $html = true, $email = null)
  { 
    $errors = false;
    $attributes = $form->getAttributes();
    $notificationSettings = $attributes['notificationSettings'];
    $toEmails = ArrayHelper::stringToArray($notificationSettings['emailSettings']['notifyEmail']);
    
    foreach ($toEmails as $toEmail) {
      $email = new EmailModel();
      $emailSettings    = craft()->email->getSettings();

      $email->fromEmail = $emailSettings['emailAddress'];
      $email->replyTo   = $emailSettings['emailAddress'];
      $email->sender    = $emailSettings['emailAddress'];
      $email->fromName  = $form->name;
      $email->toEmail   = $toEmail;
      $email->subject   = $notificationSettings['emailSettings']['emailSubject'];
      $email->body      = $message;

      // TODO: Add attachments to emails
      // if ($postUploads) {
      //   foreach ($postUploads as $key => $value) {
      //     $file = \CUploadedFile::getInstanceByName($key);
      //     $email->addAttachment($file->getTempName(), $file->getName(), 'base64', $file->getType());
      //   }
      // }
      
      if (!craft()->email->sendEmail($email)) {
        $errors = true;
      }
    }
    
    return $errors ? false : true;
  }
}
