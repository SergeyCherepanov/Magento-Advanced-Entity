<?php
/**
 * @category    Ch
 * @package     Ch_Entity
 * @copyright   Copyright (c) 2011 Sergey Cherepanov. (http://www.cherepanov.org.ua)
 * @license     http://www.gnu.org/licenses/gpl.html GNU GENERAL PUBLIC LICENSE v3.0
 */

class Ch_Entity_Adminhtml_AttributeController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Choose entity form
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->initLayoutMessages('adminhtml/session');
        $this->renderLayout();
    }

    /**
     * Choose entity form
     *
     * @return void
     */
    public function gridAction()
    {
        $this->loadLayout(array('default', 'adminhtml_attribute_grid'));
        $this->renderLayout('root');
    }

    /**
     * Add new attribute
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit attribute
     *
     * @return void
     */
    public function editAction()
    {
        $entityTypeId = $this->getRequest()->getParam('entity_type');
        $attributeId  = $this->getRequest()->getParam('attribute_id');

        /** @var $attributeModel Ch_Entity_Model_Resource_Entity_Attribute */
        $attributeModel = Mage::getModel('ch_entity/resource_entity_attribute');
        $attributeModel->load($attributeId);

        if (!$attributeModel->getEntityTypeId() && $entityTypeId){
            /** @var $entity Mage_Eav_Model_Entity */
            $entity = Mage::getModel('eav/entity');
            $entity->setType($entityTypeId);
            $attributeModel->setEntityType($entity->getEntityType());
            $attributeModel->setEntityTypeId($entity->getTypeId());
        }

        Mage::register('entity_attribute', $attributeModel);

        $this->loadLayout();
        $this->initLayoutMessages('adminhtml/session');

        $this->_addContent($this->getLayout()->createBlock('ch_entity/adminhtml_attribute_edit'));
        $this->_addLeft($this->getLayout()->createBlock('ch_entity/adminhtml_attribute_edit_tabs'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        try {
            $entityTypeId = $this->getRequest()->getParam('entity_type_id');
            $attributeId  = $this->getRequest()->getParam('attribute_id');
            $data         = $this->getRequest()->getPost();

            $helper = $this->_getHelper();

            if (empty($data)) {
                Mage::throwException($this->_getHelper()->__('Wrong request.'));
            }

            /** @var $session Mage_Admin_Model_Session */
            $session = Mage::getSingleton('adminhtml/session');

            /* @var $attributeModel Ch_Entity_Model_Resource_Entity_Attribute */
            $attributeModel = Mage::getResourceModel('ch_entity/entity_attribute');

            if ($attributeId) {
                $attributeModel->load($attributeId);
                if (!$attributeModel->getId()) {
                    Mage::throwException($helper->__('This Attribute no longer exists'));
                }
                $data['attribute_code'] = $attributeModel->getAttributeCode();
                $data['frontend_input'] = $attributeModel->getFrontendInput();
            }

            if (!$attributeModel->getEntityTypeId() && $entityTypeId){
                /** @var $entity Mage_Eav_Model_Entity */
                $entity = Mage::getModel('eav/entity');
                $entity->setType($entityTypeId);
                if ($entity->getTypeId()) {
                    $attributeModel->setEntityType($entity->getEntityType());
                    $attributeModel->setEntityTypeId($entity->getTypeId());
                } else {
                    Mage::throwException($helper->__('This Entity no longer exists'));
                }
            } else {
                Mage::throwException($helper->__('Undefined entity type.'));
            }

            if (isset($data['attribute_code'])) {
                $validatorAttrCode = new Zend_Validate_Regex(array('pattern' => '/^[a-z][a-z_0-9]{1,254}$/'));
                if (!$validatorAttrCode->isValid($data['attribute_code'])) {
                    Mage::throwException($helper->__('Attribute code is invalid. Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.'));
                }
            }

            // Validate frontend_input
            if (isset($data['frontend_input'])) {
                /** @var $validatorInputType Mage_Eav_Model_Adminhtml_System_Config_Source_Inputtype_Validator */
                $validatorInputType = Mage::getModel('eav/adminhtml_system_config_source_inputtype_validator');
                if (!$validatorInputType->isValid($data['frontend_input'])) {
                    foreach ($validatorInputType->getMessages() as $message) {
                        $session->addError($message);
                    }
                    $this->_redirect('*/*/edit', array('attribute_id' => $attributeId, '_current' => true));
                    return;
                }
            }
            $data['backend_type'] = $attributeModel->getBackendTypeByInput($data['frontend_input']);

            $attributeModel->setData($data);
            $attributeModel->save();

            $this->_getSession()->addSuccess('Attribute saved successfully.');
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->_getHelper()->__("Can't save attribute."));
        }

        $this->_redirect('*/*');
    }

    /**
     * Delete entity
     *
     * @return void
     */
    public function deleteAction()
    {
        $this->_redirect('*/*');
    }
}