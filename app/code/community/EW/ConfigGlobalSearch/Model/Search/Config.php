<?php

class EW_ConfigGlobalSearch_Model_Search_Config extends Varien_Object
{
    /** @var  Mage_Adminhtml_Model_Config */
    protected $_config;

    /**
     * Determine if field should be shown in a global context
     *
     * @param $field
     * @return bool
     */
    protected function _canShowField($field) {
        $ifModuleEnabled = trim((string)$field->if_module_enabled);
        if ($ifModuleEnabled && !Mage::helper('Core')->isModuleEnabled($ifModuleEnabled)) {
            return false;
        }

        return (int)$field->show_in_default;
    }

    /**
     * Add result if it is a match
     *
     * @param $results
     * @param $title
     * @param $type
     * @param $sectionId
     * @param $pathTab
     * @param $pathSection
     * @param string $pathField
     */
    protected function _addIfMatch(&$results, $title, $type, $sectionId, $groupId, $fieldId, $pathTab, $pathSection, $pathField = '') {
        $title = (string)$title;
        $searchTitle = strtolower($title);
        $pathTab = (string)$pathTab;
        $pathSection = (string)$pathSection;
        $pathField = (string)$pathField;

        $query = strtolower($this->getQuery());

        if(strpos($searchTitle, $query) === false) {
            return; // not a match
        }

        //Mage::log(sprintf('config/%s/%s/%s', $sectionId, $groupId, $fieldId));
        $helper = $this->_config->getAttributeModule($sectionId, $groupId, $fieldId);
        /* @var $helperInstance Mage_Core_Helper_Abstract */
        $helperInstance = Mage::helper($helper);
        //Mage::log($helper);

        $path = sprintf(
            '%s -> %s',
            $helperInstance->__($pathTab),
            $helperInstance->__($pathSection)
        );
        if(!empty($pathField)) {
            $path .= ' -> ' . $helperInstance->__($pathField);
        }

        //@TODO: translate labels
        $results[] = array(
            'id'            => sprintf('config/%s/%s', $groupId, $fieldId),
            'type'          => Mage::helper('adminhtml')->__('System Config ' . $type),
            'name'          => $helperInstance->__($title),
            'description'   => $path,
            'url' => Mage::helper('adminhtml')->getUrl(
                '*/system_config/edit',
                array('section' => $sectionId)
            )
        );
    }

    /**
     * Load config search results
     *
     * @return EW_ConfigGlobalSearch_Model_Search_Config
     */
    public function load()
    {
        $arr = array();

        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }

        $this->_config = Mage::getSingleton('adminhtml/config');
        $sections = (array)$this->_config->getSections();
        $session = Mage::getSingleton('admin/session');

        foreach ($sections as $sectionId => $section) {
            /* @var $section Varien_Simplexml_Element */
            if (!$this->_canShowField($section)) {
                continue;
            }

            //check ACL for this section
            $resourceLookup = "admin/system/config/{$sectionId}";
            if ($session->getData('acl') instanceof Mage_Admin_Model_Acl) {
                $resourceId = $session->getData('acl')->get($resourceLookup)->getResourceId();
                if (!$session->isAllowed($resourceId)) {
                    continue;
                }
            }

            foreach ($section->groups as $groups){
                $groups = (array)$groups;

                /* @var $group Varien_Simplexml_Element */
                foreach ($groups as $groupId => $group){
                    if (!$this->_canShowField($group)) {
                        continue;
                    }

                    $this->_addIfMatch($arr, $group->label, 'Group', $sectionId, $groupId, null, $section->label, $group->label);

                    foreach($group->fields as $groupFields) {
                        $groupFields = (array)$groupFields;

                        foreach($groupFields as $fieldId => $field) {
                            if (!$this->_canShowField($field)) {
                                continue;
                            }

                            $this->_addIfMatch($arr, $field->label, 'Field', $sectionId, $groupId, $fieldId, $section->label, $group->label, $field->label);
                        } //end looping fields
                    } //end looping groupFields
                } //end looping groups
            } //end looping section groups
        } //end looping sections

        $this->setResults($arr);

        return $this;
    }
}