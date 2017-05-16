<?php

class Testimonial_MageDoc_Model_Order_Pdf_Supply extends Testimonial_MageDoc_Model_Order_Pdf_Assembly
{

    public function getPdf($retailers = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('assembly');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        $page = $this->newPage();

        Mage::getSingleton('magedoc/order')->setOrderCurrencyCode('UAH');

        $this->_setFontBold($page, 10);
        $this->y -= 10;
        $page->drawText(Mage::helper('magedoc')->__('Routing sheet from %s', Mage::helper('core')->formatDate(Mage::app()->getLocale()->date(), 'medium', false)), 35, $this->y, 'UTF-8');
        $this->y -= 10;

        foreach ($retailers as $retailer) {
            if ($retailer->getStoreId()) {
                Mage::app()->getLocale()->emulate($retailer->getStoreId());
                Mage::app()->setCurrentStore($retailer->getStoreId());
            }

            if ($this->y < 300) {
                $page = $this->newPage(array('table_header' => false));
            }

            $this->_setFontBold($page, 10);

            $this->y -= 10;
            $page->drawText(Mage::helper('magedoc')->__('%s', $retailer->getName()), 35, $this->y, 'UTF-8');
            $this->y -= 10;

            $this->_drawDocumentBody($page, $retailer);

            if ($retailer->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
        }

        $this->_afterGetPdf();

        return $pdf;
    }

    protected function _drawDocumentBody($page, $retailer)
    {
        $this->_drawTableHeader($page);
        $this->_drawItems($retailer->getItems(), $page, $retailer);
        //$this->_drawItems($retailer->getInquiries(), $page, $retailer);
        $this->_drawCustomTotals($page, array(array(
            'label' => Mage::helper('magedoc')->__('Order Total'),
            'value' => $retailer->getTotal())
        ));

        return $this;
    }

    public function drawTotals($totals)
    {
        if ($this->y < 300) {
            $page = $this->newPage(array('table_header' => false));
        }else{
            $page = end($this->_getPdf()->pages);
        }
        $this->_drawCustomTotals($page, $totals);
    }

    protected function _drawCustomTotals($page, $totals = array())
    {
        foreach ($totals as $total) {
            $this->y -= 10;
            $totalName = $total['label'];

            $totalValue = Mage::getSingleton('magedoc/order')->formatPriceTxt($total['value']);
            $font = $this->_setFontBold($page);
            $feed = 475 - $this->widthForStringUsingFontSize($totalName, $font, '10');
            $page->drawText($totalName, $feed, $this->y, 'UTF-8');
            $feed = 565 - $this->widthForStringUsingFontSize($totalValue, $font, '10');
            $page->drawText($totalValue, $feed, $this->y, 'UTF-8');
        }
        $this->y -= 10;
    }

    protected function _drawItems($items, $page, $retailer)
    {
        $order = Mage::getSingleton('magedoc/order');
        /* Add body */
        foreach ($items as $item) {
            $item->setOrderItem($item);
            if ($item->getParentItem()) {
                continue;
            }

            if ($this->y < 15) {
                $page = $this->newPage(array('table_header' => true));
            }

            /* Draw item */
            $page = $this->_drawItem($item, $page, $order);
        }
        return $page;
    }
}
