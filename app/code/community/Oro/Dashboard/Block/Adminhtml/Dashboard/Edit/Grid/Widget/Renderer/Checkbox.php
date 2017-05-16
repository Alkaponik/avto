<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

class Oro_Dashboard_Block_Adminhtml_Dashboard_Edit_Grid_Widget_Renderer_Checkbox
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox
{

    /**
     * Renders header of the column
     *
     * @return string
     */
    public function renderHeader()
    {
        $html = parent::renderHeader();
        if ($this->getColumn()->getColumnLabel()) {
            $html = "<span class='sort-title'>" . $this->getColumn()->getColumnLabel() . "  " . $html . "</span>";
        }

        return $html;
    }
}
