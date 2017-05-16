<?php

class Testimonial_MageDoc_Model_Order_Pdf_Assembly extends Mage_Sales_Model_Order_Pdf_Abstract
{
    protected $_totals;
    protected $_hiddenTotals = array();
    protected $_pageIndex = -1;
    protected $_offsetX = 55;

    const START_Y = 825;
    const MODE_ASSEMBLIES = 1;
    const MODE_EXPENDABLES = 2;
    const DEFAULT_FONT_SIZE = 10;
    const FREE_SHIPPING_CARRIER_CODE = 'multipletablerates';
    const FREE_SHIPPING_PAYMENT_METHOD = 'checkmo';
    const FREE_SHIPPING_ORDER_AMOUNT = 1000;

    public function getPdf($orders = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('assembly');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        //$page = $this->newPage();
        //Zend_Barcode::setBarcodeFont(Mage::getBaseDir() . '/lib/GaramondFont/Code128bWin.ttf');

        foreach ($orders as $order) {
            $order->setOrder($order);
            if ($order->getStoreId()) {
                Mage::app()->getLocale()->emulate($order->getStoreId());
                Mage::app()->setCurrentStore($order->getStoreId());
            }

            $orderPrefix = substr($order->getRealOrderId(), 0, 3);
            $storePrefix = mb_substr(Mage::app()->getStore()->getName(), 0, 1, 'UTF-8');
            $orderSuffix = $storePrefix.substr($order->getRealOrderId(), 3);

            if ($this->getMode() & self::MODE_ASSEMBLIES) {
                $this->setIsPublic(true);
                $this->_hideTotals(array('cost', 'margin'));
                if (1 || $this->y < 300) {
                    $page = $this->newPage(array('table_header' => false));
                }

                $this->_drawHeader($page, $order, 'Packing list for Order # %s', $orderPrefix, $orderSuffix);
                $this->_drawBarcode($page, $order);
                $this->_drawDocumentBody($page, $order);
            }

            if ($this->getMode() & self::MODE_EXPENDABLES) {
                $this->setIsPublic(true);
                //$this->_showTotals();
                $page = $this->newPage(array('table_header' => false));
                $this->_drawHeader($page, $order, 'Warehouse expendable for Order # %s', $orderPrefix, $orderSuffix);
                $this->_drawBarcode($page, $order);
                $this->_drawDocumentBody($page, $order);
            }

            if ($order->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
        }

        $this->_afterGetPdf();

        return $pdf;
    }

    protected function _drawDocumentBody($page, $order)
    {
        /* Add head */
        $this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId()));

        $this->_drawTableHeader($page);

        $page = $this->_drawItems($order->getAllItems(), $page, $order);

        $this->_drawInquiries($page, $order);

        /* Add totals */
        $page = $this->insertTotals($page, $order);
    }

    protected function _drawInquiries($page, $order)
    {
        foreach ($order->getVehiclesCollection() as $vehicle)
        {
            if ($vehicle->getInquiriesCollection()->getSize()){
                $page = $this->_drawItems($vehicle->getInquiriesCollection(), $page, $order);
            }
        }
        return $this;
    }

    protected function _drawItems($items, $page, $order)
    {
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

    /**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = self::START_Y;

        if (!empty($settings['table_header'])) {
            $this->_drawTableHeader($page);
        }

        $this->_pageIndex++;

        return $page;
    }

    protected function _drawTableHeader($page)
    {
        $isPublic = $this->getIsPublic();
        $this->_setFontRegular($page);
        //$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle($this->_offsetX - 10, $this->y, 570, $this->y-15);
        $this->y -=10;

        //$page->setFillColor(new Zend_Pdf_Color_Rgb(0.4, 0.4, 0.4));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $page->drawText(Mage::helper('sales')->__('Products'), $this->_offsetX, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('SKU'), 265, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Price'), 380, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Qty'), 420, $this->y, 'UTF-8');
        if (!$isPublic){
            $page->drawText(Mage::helper('sales')->__('Retailer'), 465, $this->y, 'UTF-8');
        }
        $page->drawText(Mage::helper('sales')->__('Subtotal'), 525, $this->y, 'UTF-8');

        $this->y -=20;
        return $page;
    }

    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof Mage_Sales_Model_Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        //Y = 825
        //$currentY = 825
        $currentY = $this->y;

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page);

        if ($putOrderId) {
            //$page->drawText(Mage::helper('sales')->__('Order # ').$order->getRealOrderId(), 35, $currentY - 10, 'UTF-8');
        }
        //$page->drawText(Mage::helper('sales')->__('Order Date: ') . date( 'D M j Y', strtotime( $order->getCreatedAt() ) ), 35, 760, 'UTF-8');
        //$page->drawText(Mage::helper('sales')->__('Order Date: ') . Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), 35, $currentY - 20, 'UTF-8');

        //$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle($this->_offsetX - 10, $currentY - 10, 275, $currentY - 25);
        $page->drawRectangle(275, $currentY - 10, 570, $currentY - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));

        /* Payment */
        $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true)
            ->toPdf();
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key=>$value){
            if (strip_tags(trim($value))==''){
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));

            $shippingMethod  = $order->getShippingDescription();
            $shippingMethod = Mage::helper('core/string')->str_split($shippingMethod, 65, true, true);
        }

        $customerNote = Mage::helper('core/string')->str_split($order->getCustomerNote(), 65, true, true);

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);
        //$page->drawText(Mage::helper('sales')->__('SHIP TO:'), 35, $currentY - 40 , 'UTF-8');

        $page->drawText(Mage::helper('sales')->__('Order # ').$this->getOrderManagerInitials($order).'-'.$order->getRealOrderId(), $this->_offsetX, $currentY - 20, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Order Date: ') . Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), 285, $currentY - 20, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Packing Date: ') . Mage::helper('core')->formatDate(Mage::app()->getLocale()->date(), 'medium', false), 455, $currentY - 20, 'UTF-8');

        if (!$order->getIsVirtual()) {
            //$page->drawText(Mage::helper('sales')->__('Shipping Method:'), 285, $currentY - 20 , 'UTF-8');
        }
        else {
            $page->drawText(Mage::helper('sales')->__('Payment Method:'), 285, $currentY - 20 , 'UTF-8');
        }

        if (!$order->getIsVirtual()) {
            //$y = $currentY - 25 - (max(count($billingAddress), count($shippingAddress)) * 10 + ($order->getPayment()->getMethod() == 'bankpayment' ? 50 : 10));
            $y = $currentY - 25 - max(
                count($shippingAddress) * 10 + 10,
                count($payment) * 10
                    + count($shippingMethod) * 10
                    + count($customerNote) * 10
                    + 25);
        }
        else {
            $y = $currentY - 25 - (count($billingAddress) * 10 + 5);
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle($this->_offsetX - 10, $currentY - 25, 570, $y);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);

        $this->y = $currentY - 35;
        foreach ($shippingAddress as $value) {
            if ($value !== '') {
                $page->drawText(strip_tags(ltrim($value)), $this->_offsetX, $this->y, 'UTF-8');
                $this->y -= 10;
            }
        }
        $this->y -= 5;

        $this->_setFontRegular($page);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

        $paymentLeft = 285;
        $yPayments = $currentY - 35;

        foreach ($payment as $value){
            if (trim($value)!=='') {
                $page->drawText(strip_tags(trim($value)), $paymentLeft, $yPayments, 'UTF-8');
                $yPayments -=10;
            }
        }

        if (!$order->getIsVirtual()) {
            $yShipments = $yPayments - 5;

            foreach ($shippingMethod as $shippingMethodLine){
                $page->drawText($shippingMethodLine, 285, $yShipments, 'UTF-8');
                $yShipments -= 10;
            }

            //$yShipments = $this->y;

            if ($order->getShippingCarrier()->getCarrierCode() == self::FREE_SHIPPING_CARRIER_CODE
                && $order->getPayment()->getMethod() == self::FREE_SHIPPING_PAYMENT_METHOD
                && $order->getGrandTotal() >= self::FREE_SHIPPING_ORDER_AMOUNT){
                $totalShippingChargesText = Mage::helper('magedoc')->__('Free Shipping');
            }else{
                $totalShippingChargesText = "(" . Mage::helper('sales')->__('Total Shipping Charges') . " " . $order->formatPriceTxt($order->getShippingAmount()) . ")";
            }

            $page->drawText($totalShippingChargesText, 285, $yShipments, 'UTF-8');
            $yShipments -=10;

            if ($order->getCustomerNote()){
                foreach ($customerNote as $customerNoteLine){
                    $page->drawText($customerNoteLine, 285, $yShipments, 'UTF-8');
                    $yShipments -=10;
                }
                $yShipments +=5;
            }

            $tracks = array();
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(380, $yShipments, 380, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page);
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(Mage::helper('sales')->__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Title'), 290, $yShipments - 10, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Number'), 385, $yShipments - 10, 'UTF-8');

                $yShipments -=20;
                $this->_setFontRegular($page, 6);
                foreach ($tracks as $track) {

                    $CarrierCode = $track->getCarrierCode();
                    if ($CarrierCode!='custom')
                    {
                        $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($CarrierCode);
                        $carrierTitle = $carrier->getConfigData('title');
                    }
                    else
                    {
                        $carrierTitle = Mage::helper('sales')->__('Custom Value');
                    }

                    //$truncatedCarrierTitle = substr($carrierTitle, 0, 35) . (strlen($carrierTitle) > 35 ? '...' : '');
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    //$page->drawText($truncatedCarrierTitle, 285, $yShipments , 'UTF-8');
                    $page->drawText($truncatedTitle, 300, $yShipments , 'UTF-8');
                    $page->drawText($track->getNumber(), 395, $yShipments , 'UTF-8');
                    $yShipments -=10;
                }
            } else {
                $yShipments -= 10;
            }

            $currentY = min($this->y, $yShipments);

            // replacement of Shipments-Payments rectangle block
            //$page->drawLine(25, $this->y + 15, 25, $currentY);
            //$page->drawLine(25, $currentY, 570, $currentY);
            //$page->drawLine(570, $currentY, 570, $this->y + 15);

            $this->y = $currentY;
            //$this->y -= 5;
        }
    }

    /**
     * Return total list
     *
     * @param  Mage_Sales_Model_Abstract $source
     * @return array
     */
    protected function _getTotalsList($source)
    {
        if (!isset($this->_totals)){
            $this->_totals = parent::_getTotalsList($source);
        }
        $totals = array();
        foreach ($this->_totals as $total){
            if (!in_array($total->getSourceField(), $this->_hiddenTotals)){
                $totals []= $total;
            }
        }
        return $totals;
    }

    protected function _hideTotals($totalKeys)
    {
        $this->_hiddenTotals = array_merge($this->_hiddenTotals, $totalKeys);
        return $this;
    }

    protected function _showTotals($totalKeys = array())
    {
        if (empty($totalKeys)){
            $this->_hiddenTotals = array();
        }else{
            foreach ($this->_hiddenTotals as $key => $totalSourceField){
                if (in_array($totalSourceField, $totalKeys)){
                    unset($this->_hiddenTotals[$key]);
                }
            }
        }
        return $this;
    }

    /**
     * Set font as regular
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($object, $size = null)
    {
        if (is_null($size)){
            $size = $this->getDefaultFontSize();
        }
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/GaramondFont/gara.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = null)
    {
        if (is_null($size)){
            $size = $this->getDefaultFontSize();
        }
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/GaramondFont/garabd.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as italic
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($object, $size = null)
    {
        if (is_null($size)){
            $size = $this->getDefaultFontSize();
        }
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/GaramondFont/garai.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    public function getDefaultFontSize()
    {
        return self::DEFAULT_FONT_SIZE;
    }

    public function getOrderManagerInitials($order)
    {
        $initials = explode(' ', $order->getManager()->getName());
        foreach ($initials as $key => $value){
            $initials[$key] = mb_strtoupper(mb_substr($value, 0, 1, 'UTF-8'), 'UTF-8');
        }
        return implode('', $initials);
    }

    protected function _drawHeader($page, $order, $title, $orderPrefix, $orderSuffix)
    {
        $this->_setFontBold($page, 10);
        $this->y -= 10;
        $page->drawText(Mage::helper('magedoc')->__($title, $this->getOrderManagerInitials($order).'-'.$orderPrefix), $this->_offsetX, $this->y, 'UTF-8');
        $this->_setFontBold($page, 50);
        $this->y -= 20;
        $page->drawText($orderSuffix, 285, $this->y, 'UTF-8');
        $page->rotate( $this->_offsetX - 15, 650, M_PI/2);
        $page->drawText($orderSuffix, $this->_offsetX - 15, 650, 'UTF-8');
        $page->rotate( $this->_offsetX - 15, 650, -M_PI/2);

        return $this;
    }

    protected function _drawBarcode($page, $order)
    {
        $pdf = $this->_getPdf();
        $barcodeOptions = array(
            'text'          => $order->getRealOrderId(),
            'DrawText'      => false,

        );
        $rendererOptions = array(
            'topOffset' => 15,
            'leftOffset' => 445,
            'ModuleSize' => 0.75
        );
        Zend_Barcode::factory('code39', 'pdf',
            $barcodeOptions, $rendererOptions)->setResource($pdf, $this->_pageIndex)->draw();

    }

    public function getOffsetX()
    {
        return $this->_offsetX;
    }
}
