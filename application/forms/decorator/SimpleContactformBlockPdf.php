<?php 
/**
 * This wraps the content with the tcpdf-friendly pdf-template
 * @author nico
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
class Zend_Form_Decorator_SimpleContactformBlockPdf extends Zend_Form_Decorator_Abstract
{

    protected $_format = <<<SOT
    <!--start_header_pdf_template-->
    <tr>
		<td height="1" align="left"></td>
	</tr>
	<tr>
		<th bgcolor="#D8D8D8" height="25px" width="820">
			<b> %s </b>
		</th>
	</tr>
	<tr>
		<td height="1" align="left"></td>
	</tr>
	<!--end_header_pdf_template-->
	<tr>
        <td>
        %s
        </td>
	</tr>
SOT;


    			
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
        $legend  = $this->getLegend();

        if (null !== $legend) {
            if (null !== ($translator = $element->getTranslator())) {
                $legend = $translator->translate($legend);
            }
        }

        $result = sprintf($this->_format,
            $legend,
            $content
        );

        return $result;        
    }
}