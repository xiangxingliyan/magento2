<?php

namespace Ave40\Base\Controller\Demiware;

use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\InputException;
use Ave40\Base\Model\DemiwareFactory;
use Ave40\Base\Model\Demiware;

class EditPost extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var Session
     */
    protected $session;

    /** @var EmailNotificationInterface */
    private $emailNotification;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var Mapper
     */
    private $customerMapper;

    private $demiwareFactory;

    private $resourceModel;

    private $_demiwareHelper;

    protected $requiredFields = ['first_name', 'last_name', 'country', 'email', 'street', 'phone', 'smoke_for_years', 'reason'];

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param Validator $formKeyValidator
     * @param CustomerExtractor $customerExtractor
     * @param DemiwareFactory $demiwareFactory
     * @param \Ave40\Base\Model\ResourceModel\Demiware $resourceModel
     * @param \Ave40\Base\Helper\Demiware\Demiware $demiwareHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Validator $formKeyValidator,
        CustomerExtractor $customerExtractor,
        DemiwareFactory $demiwareFactory,
        \Ave40\Base\Model\ResourceModel\Demiware $resourceModel,
        \Ave40\Base\Helper\Demiware\Demiware $demiwareHelper
    )
    {
        parent::__construct($context);
        $this->session = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerExtractor = $customerExtractor;
        $this->demiwareFactory = $demiwareFactory;
        $this->resourceModel = $resourceModel;
        $this->_demiwareHelper = $demiwareHelper;
    }

    /**
     * Post Customer Info
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());
        if (!$this->session->isLoggedIn() || !$validFormKey) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect->setPath('base/demiware/success');
            return $resultRedirect;
        }
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_demiwareHelper->demiwareIsActive()) {
                    throw new \Exception('Expired Promotion!');
                }
                $customerId = $this->session->getCustomerId();
                if (empty($customerId)) {
                    throw new \Exception('Customer ID error !');
                }
                /** @var \Ave40\Base\Model\Demiware $model */
                $model = $this->demiwareFactory->create();
                $params = [
                    'customer_id' => $customerId,
                    'demiware_code' => $this->_demiwareHelper->getDemiwareCode()
                ];
                $customerInfo = $model->loadByField($params);
                if (empty($customerInfo)) {
                    $data = $this->getRequest()->getPostValue();
                    $errorField = [];
                    foreach ($data as $key => $value) {
                        if (in_array($key, $this->requiredFields) && empty(trim($value))) {
                            $errorField[] = $key;
                            continue;
                        }
                        $model->setData($key, $value);

                    }
                    if (!empty($errorField)) {
                        throw new \Exception('The ' . implode(',', $errorField) . ' field is required.');
                    }
                    $model->setCustomerId($customerId);
                    $model->setLastUpdatedAt(date('Y-m-d H:i:s'));
                    $this->resourceModel->save($model);
                    $this->messageManager->addSuccess(__('Success.'));
                } else {
                    throw new \Exception('You have already applied for');
                }
            } catch (InputException $e) {
                $this->messageManager->addError($e->getMessage());
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addError($error->getMessage());
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->session->setCustomerFormData($this->getRequest()->getPostValue());
        }
        return $resultRedirect->setPath('base/demiware/');
    }
}
