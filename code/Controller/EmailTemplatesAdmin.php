<?php
namespace LeKoala\EmailTemplates\Controller;
use LeKoala\EmailTemplates\Model\EmailTemplate;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\View\Requirements;


/**
 * EmailTemplatesAdmin
 *
 * @author lekoala
 */
class EmailTemplatesAdmin extends ModelAdmin
{

    private static $managed_models = array(
        'LeKoala\EmailTemplates\Model\EmailTemplate',
        'LeKoala\EmailTemplates\Model\SentEmail',
    );
    private static $url_segment = 'emails';
    private static $menu_title = 'Emails';
    private static $menu_icon = 'lekoala/silverstripe-email-templates: images/mail.png';
    private static $allowed_actions = array(
        'ImportForm',
        'SearchForm',
        'PreviewEmail',
        'ViewSentEmail',
        'doSendTestEmail',
    );

    public function subsiteCMSShowInMenu()
    {
        return true;
    }

    public function getSearchContext()
    {
        $context = parent::getSearchContext();

        $categories = EmailTemplate::get()->column('Category');
        $context->getFields()->replaceField('q[Category]', $dd = new DropdownField('q[Category]',
            'Category', ArrayLib::valuekey($categories)));
        $dd->setEmptyString('');

        return $context;
    }

    public function getList()
    {
        $list = parent::getList();

        return $list;
    }

    public function PreviewEmail()
    {
        // Prevent CMS styles to interfere with preview
        Requirements::clear();

        $id = (int) $this->getRequest()->getVar('id');

        /* @var $emailTemplate EmailTemplate */
        $emailTemplate = EmailTemplate::get()->byID($id);

        $html = $emailTemplate->renderTemplate(true, true);

        Requirements::restore();

        return $html;
    }

    public function ViewSentEmail()
    {
        // Prevent CMS styles to interfere with preview
        Requirements::clear();

        $id = (int) $this->getRequest()->getVar('id');

        /* @var $sentEmail SentEmail */
        $sentEmail = SentEmail::get()->byID($id);

        $html = $sentEmail->Body;

        Requirements::restore();

        return $html;
    }

    public function doSendTestEmail()
    {
        $request = $this->getRequest();

        $id = (int) $request->requestVar('EmailTemplateID');
        if (!$id) {
            throw new \Exception('Please define EmailTemplateID parameter');
        }

        $emailTemplate = EmailTemplate::get()->byID($id);
        if (!$emailTemplate) {
            throw new \Exception("Template is not found");
        }
        $sendTestEmail = $request->requestVar('SendTestEmail');

        if (!$sendTestEmail) {
            throw new \Exception('Please define SendTestEmail parameter');
        }

        $email = $emailTemplate->getEmail();
        $email->setTo($sendTestEmail);

        $res = $email->send();

        if ($res) {
            return 'Test email sent to ' . $sendTestEmail;
        }
        return 'Failed to send test to ' . $sendTestEmail;
    }
}
