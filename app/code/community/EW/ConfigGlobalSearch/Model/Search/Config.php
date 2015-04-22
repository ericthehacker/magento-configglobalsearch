<?php

class EW_ConfigGlobalSearch_Model_Search_Config extends Varien_Object
{
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
    protected function _addIfMatch(&$results, $title, $type, $sectionId, $pathTab, $pathSection, $pathField = '') {
        $title = (string)$title;
        $searchTitle = strtolower($title);
        $pathTab = (string)$pathTab;
        $pathSection = (string)$pathSection;
        $pathField = (string)$pathField;

        $query = strtolower($this->getQuery());

        if($type == 'section') {
            Mage::log($searchTitle);
        }

        if(strpos($searchTitle, $query) === false) {
            return; // not a match
        }

        $path = sprintf('%s -> %s', $pathTab, $pathSection);
        if(!empty($pathField)) {
            $path .= ' -> ' . $pathField;
        }

        //@TODO: translate labels
        $results[] = array(
            'id'            => sprintf('config/%s/%s', $type, $sectionId),
            'type'          => Mage::helper('adminhtml')->__('System Config ' . $type),
            'name'          => $title,
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

        /* @var $config Mage_Adminhtml_Model_Config */
        $config = Mage::getSingleton('adminhtml/config');
        $sections = (array)$config->getSections();
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
                foreach ($groups as $group){
                    if (!$this->_canShowField($group)) {
                        continue;
                    }

                    $this->_addIfMatch($arr, $group->label, 'Group', $sectionId, $section->label, $group->label);

                    foreach($group->fields as $groupFields) {
                        $groupFields = (array)$groupFields;

                        foreach($groupFields as $field) {
                            if (!$this->_canShowField($field)) {
                                continue;
                            }

                            $this->_addIfMatch($arr, $field->label, 'Field', $sectionId, $section->label, $group->label, $field->label);
                        } //end looping fields
                    } //end looping groupFields
                } //end looping groups
            } //end looping section groups
        } //end looping sections

        $this->setResults($arr);

        return $this;
    }
}