<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m171220_085611_freeform_RemoveAssetSourceForeignKey extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        craft()
            ->db
            ->createCommand()
            ->dropForeignKey(
                'freeform_fields',
                'assetSourceId'
            );

        return true;
	}
}
