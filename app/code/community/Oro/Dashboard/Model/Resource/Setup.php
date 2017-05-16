<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    /** Get config timezone from DB
     *
     * @return int
     */
    public function getStoreTimezone()
    {
        $select = $this->getConnection()->select();
        $select->from($this->getTable('core/config_data'), 'value');
        $select->where("scope_id = :scope_id AND path = :path AND scope = :scope");
        $binds = array('scope_id' => 0, 'path' => 'general/locale/timezone',
            'scope' => Mage_Adminhtml_Block_System_Config_Form::SCOPE_DEFAULT);

        return $this->getConnection()->fetchOne($select,$binds);
    }
}
