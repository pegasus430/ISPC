<?php 
/**
 * 
 * @author claudiu
 * @version rc1 
 * @since 2017.12.07
 *  Maria:: Migration CISPC to ISPC 22.07.2020
 */
class Zend_Form_Decorator_SimpleContactformBlock extends Zend_Form_Decorator_Abstract
{
    
    /**
     * TODO :
     *  - create a new format from ipad.
     *  - do this new ipad style with divs to work on pc also.
     * 
     * @var string
     */
    
    protected $_format = <<<SOT
        <!-- start box %s-->
        <div class="contactform_dragvbox %s"  id="%s">
            
            <div class="%s">
                %s
                <button type="button" class="btnControl" onclick="boxToggle(this); return false;"></button>
            </div>
        
            <div id="%s_content" class="%s">
                <div class="inputs">
                    <div class="container">
                        %s
                    </div>
                </div>
            </div>
        </div> <!-- end box %s-->
SOT;

    /*
    protected $_format = <<<SOT
        <!-- start box %s-->
        <div class="contactform_dragvbox"  id="%s">
            <h2>%s</h2>
            <div id="%s_content" class="contactform_dragvbox_content %s">
                %s
            </div>
        </div> <!-- end box %s-->
SOT;
    */
    			
    /**
     * @var string
     */
    protected $_legend;
    
    /**
     * Set legend
     *
     * @param  string $value
     * @return Zend_Form_Decorator_Fieldset
     */
    protected $stripAttribs = [];
    
    public function setLegend($value)
    {
        $this->_legend = (string) $value;
        return $this;
    }
    
    /**
     * Get legend
     *
     * @return string
     */
    public function getLegend()
    {
        $legend = $this->_legend;
        if ((null === $legend) && (null !== ($element = $this->getElement()))) {
            if (method_exists($element, 'getLegend')) {
                $legend = $element->getLegend();
                $this->setLegend($legend);
            }
        }
        if ((null === $legend) && (null !== ($legend = $this->getOption('legend')))) {
            $this->setLegend($legend);
            $this->removeOption('legend');
        }
    
        return $legend;
    }
    
    
    public function getOptions()
    {
        $options = parent::getOptions();
        if (null !== ($element = $this->getElement())) {
            $attribs = $element->getAttribs();
            foreach ($options as $k => &$v) {
                if (isset($attribs[$k]) && is_string($v) && is_string($attribs[$k])) {
                    $v = $attribs[$k] . ' ' .$v;
                }
            }            
            $options = array_merge($attribs, $options);
            $this->setOptions($options);
        }
        return $options;
    }
    
    
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }
    
        $legend  = $this->getLegend();
        $attribs = $this->getOptions();
        $name    = $element->getFullyQualifiedName();
        $id      = (string)$element->getId() ?: 'block-' . $element->filterName($legend);
        
    
        if (array_key_exists('id', $attribs) && $attribs['id'] != '' && $attribs['id'] != $id) {
            $id = $attribs['id'];
        }
      
    
        if (null !== $legend) {
            if (null !== ($translator = $element->getTranslator())) {
                $legend = $translator->translate($legend);
            }
        }
    

        $class = '';
        if (array_key_exists('class', $attribs) && '' !== $attribs['class']) {
            $class .= " " . $attribs['class'];
        }
        $groupHeaderClass = 'groupHeader';
        $groupHeaderClass .= $attribs['opened'] ? ' expanded' : ' collapsed';
        
        $contentClass = 'contactform_dragvbox_content besuch';
        $contentClass .= $attribs['opened'] ? ' expanded' : ' collapsed';
        
        
        /*
         * this was the model made for pc/ipads/... replaced now with the one for mobile
        $result = sprintf($this->_format,
            $legend,//<!-- start box %s-->
            $id,//<div class="contactform_dragvbox"  id="%s">
            $legend,//<h2>%s</h2>
            $id, $class,  //<div id="%s_content" class="contactform_dragvbox_content %s">
            $content,//content
            $legend//<!-- end box %s-->
        );
        */
        
        
        $result = sprintf($this->_format,
            $legend, //         <!-- start box %s-->
            $class, $id, //         <div class="contactform_dragvbox %s"  id="%s">
            $groupHeaderClass, //         <div class="%s">
            $legend,//         %s
            //         <button type="button" class="btnControl" onclick="boxToggle(this); return false;"></button>
            //         </div>
            $id, $contentClass,//         <div id="%s_content" class="%s">
            //         <div class="inputs">
            //         <div class="container">
            $content,//             %s
            //             </div>
            //         </div
            //         </div>
            $legend//             </div> <!-- end box %s-->
        );
        
        
        
        return $result;        
    }
}