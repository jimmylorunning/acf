<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m171214_183312_freeform_UpdateMailchimpIntegrations extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        craft()->db
            ->createCommand()
            ->update(
                'freeform_integrations',
                [
                    'class' => 'Solspace\Freeform\Library\Pro\Integrations\MailingLists\MailChimp'
                ],
                [
                    'class' => 'Solspace\Freeform\Library\Integrations\MailingLists\Implementations\MailChimp'
                ]
            );

		return true;
	}
}
