<?php
class Testimonial_MageDoc_Adminhtml_GenericArticleController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveAction()
    {
        try {
            $data = $this->getRequest()
                ->getPost(Testimonial_MageDoc_Block_Adminhtml_GenericArticle_Grid::FORM_ELEMENTS_PREFIX);

            if (is_array($data)){
                $gaMapModel = Mage::getSingleton('magedoc/retailer_genericArticle_map');
                foreach($data as $id => $gaMap) {
                    $gaMapModel->load($id);
                    $gaMapModel->addData($gaMap);
                    $data = $gaMapModel->getData();
                    $origData = $gaMapModel->getOrigData();
                    $updated =
                        count(array_merge(array_diff_assoc($origData,$data),array_diff_assoc($data,$origData)));
                    if($updated) {
                        $gaMapModel->save();
                    }
                }
            }
        } catch(Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

        }
        $this->_redirect('*/*/',array('_current' => true));
        return;
    }

    public function suggestAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('magedoc/generic_article_map');
    }
}