<?php
/**
 * @category    Ch
 * @package     Ch_Entity
 * @copyright   Copyright (c) 2011 Sergey Cherepanov. (http://www.cherepanov.org.ua)
 * @license     http://www.gnu.org/licenses/gpl.html GNU GENERAL PUBLIC LICENSE v3.0
 */

class Ch_Entity_Model_Resource_Entity extends Mage_Eav_Model_Entity_Abstract
{
    /**
     * Initialize entity
     *
     * @param $data
     */
    public function __construct($data)
    {
        if (isset($data['entity_type_code'])) {
            $this->setType($data['entity_type_code']);
        }
        $this->setConnection('core_read', 'core_write');
        parent::__construct();
    }

    /**
     * Get default attribute source model
     *
     * @return string
     */
    public function getDefaultAttributeSourceModel()
    {
        return 'eav/entity_attribute_source_table';
    }
}
