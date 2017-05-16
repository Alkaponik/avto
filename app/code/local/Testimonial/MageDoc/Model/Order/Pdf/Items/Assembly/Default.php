<?php

class Testimonial_MageDoc_Model_Order_Pdf_Items_Assembly_Default extends Mage_Sales_Model_Order_Pdf_Items_Abstract
{
    protected $_offsetX = 35;

    /**
     * Draw item line
     *
     */
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $fontSize = 9;
        $lines  = array();
        $this->setOffsetX($pdf->getOffsetX());

        // draw Product name
        $lines[0] = array(array(
            'text' => Mage::helper('core/string')->str_split($item->getName(), 50, true, true),
            'feed' => $this->_offsetX,
            'font_size' => $fontSize
        ));

        // draw SKU
        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split($this->getSku($item), 25),
            'feed'  => 265,
            'font_size' => $fontSize
        );

        // draw Price
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getPrice()),
            'feed'  => 405,
            'font'  => 'bold',
            'align' => 'right',
            'font_size' => $fontSize
        );

        // draw QTY
        $lines[0][] = array(
            //'text'  => ($item->getQtyInvoiced()-$item->getQtyShipped()-$item->getQtyRefunded())*1,
            'text'  => $item->getQtyOrdered()*1,
            'feed'  => 435,
            'font_size' => $fontSize
        );

        if (!$this->getRenderedModel()->getIsPublic()){
        $retailer = Mage::getResourceSingleton('magedoc/retailer_collection')
            ->getItemById($item->getRetailerId());
        // draw Retailer
        $lines[0][] = array(
            'text'  => $retailer ? $retailer->getName() : '',
            'feed'  => 465,
            'font'  => 'bold',
            'align' => 'left',
            'font_size' => $fontSize
        );
        }

        // draw Subtotal
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getRowTotal()),
            'feed'  => 565,
            'font'  => 'bold',
            'align' => 'right',
            'font_size' => $fontSize
        );

        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), 70, true, true),
                    'font' => 'italic',
                    'feed' => $this->_offsetX
                );

                if ($option['value']) {
                    $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                    $values = explode(', ', $_printValue);
                    foreach ($values as $value) {
                        $lines[][] = array(
                            'text' => Mage::helper('core/string')->str_split($value, 50, true, true),
                            'feed' => 40
                        );
                    }
                }
            }
        }

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 10
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }

    public function setOffsetX($offsetX)
    {
        $this->_offsetX = $offsetX;
    }
}
