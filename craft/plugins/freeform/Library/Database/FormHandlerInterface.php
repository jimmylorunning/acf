<?php
/**
 * Freeform for Craft
 *
 * @package       Solspace:Freeform
 * @author        Solspace, Inc.
 * @copyright     Copyright (c) 2008-2016, Solspace, Inc.
 * @link          https://solspace.com/craft/freeform
 * @license       https://solspace.com/software/license-agreement
 */

namespace Solspace\freeform\Library\Database;

use Solspace\Freeform\Library\Composer\Components\Form;

interface FormHandlerInterface
{
    /**
     * @param Form   $form
     * @param string $templateName
     *
     * @return string
     */
    public function renderFormTemplate(Form $form, $templateName);

    /**
     * @return bool
     */
    public function isSpamProtectionEnabled();

    /**
     * Increments the spam block counter by 1
     *
     * @param Form $form
     *
     * @return int - new spam block count
     */
    public function incrementSpamBlockCount(Form $form);

    /**
     * @param Form $form
     */
    public function addScriptsToPage(Form $form);

    /**
     * @param Form $form
     *
     * @return string
     */
    public function getScriptOutput(Form $form);

    /**
     * Do something before the form is saved
     * Return bool determines whether the form should be saved or not
     *
     * @param Form $form
     *
     * @return bool
     */
    public function onBeforeSubmit(Form $form);

    /**
     * Do something after the form is saved
     *
     * @param Form $form
     */
    public function onAfterSubmit(Form $form);
}
