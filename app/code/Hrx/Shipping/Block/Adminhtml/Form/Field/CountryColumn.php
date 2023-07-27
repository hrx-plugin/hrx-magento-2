<?php
declare(strict_types=1);

namespace Hrx\Shipping\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class CountryColumn extends Select
{
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }

        return $this->renderHtml();
    }

    private function getSourceOptions(): array
    {
        return [
            ['value' => 'AT', 'label' => __('Austria')],
            ['value' => 'BE', 'label' => __('Belgium')],
            ['value' => 'BG', 'label' => __('Bulgaria')],
            ['value' => 'HR', 'label' => __('Croatia')],
            ['value' => 'CZ', 'label' => __('Czech Republic')],
            ['value' => 'DK', 'label' => __('Denmark')],
            ['value' => 'EE', 'label' => __('Estonia')],
            ['value' => 'FI', 'label' => __('Finland')],
            ['value' => 'FR', 'label' => __('France')],
            ['value' => 'DE', 'label' => __('Germany')],
            ['value' => 'GR', 'label' => __('Greece')],
            ['value' => 'HU', 'label' => __('Hungary')],
            ['value' => 'IE', 'label' => __('Ireland')],
            ['value' => 'IT', 'label' => __('Italy')],
            ['value' => 'LV', 'label' => __('Latvia')],
            ['value' => 'LT', 'label' => __('Lithuania')],
            ['value' => 'NL', 'label' => __('Netherlands')],
            ['value' => 'PL', 'label' => __('Poland')],
            ['value' => 'PT', 'label' => __('Portugal')],
            ['value' => 'RO', 'label' => __('Romania')],
            ['value' => 'SK', 'label' => __('Slovakia')],
            ['value' => 'SI', 'label' => __('Slovenia')],
            ['value' => 'ES', 'label' => __('Spain')],
            ['value' => 'SE', 'label' => __('Sweden')],
        ];
    }

    
 
     protected function renderHtml()
     {
         if (!$this->_beforeToHtml()) {
             return '';
         }
 
         $html = '<select multiple name="' .
             $this->getName() .
             '[]" id="' .
             $this->getId() .
             '" class="required-entry ' .
             $this->getClass() .
             '" title="' .
             $this->escapeHtml($this->getTitle()) .
             '" style="min-width:90px;" size="3"' .
             $this->getExtraParams() .
             '>';
 
         $values = $this->getValue();
         if (!is_array($values)) {
             $values = (array)$values;
         }
 
         $isArrayOption = true;
         foreach ($this->getOptions() as $key => $option) {
             $optgroupName = '';
             if ($isArrayOption && is_array($option)) {
                 $value = $option['value'];
                 $label = (string)$option['label'];
                 $optgroupName = isset($option['optgroup-name']) ? $option['optgroup-name'] : $label;
                 $params = !empty($option['params']) ? $option['params'] : [];
             } else {
                 $value = (string)$key;
                 $label = (string)$option;
                 $isArrayOption = false;
                 $params = [];
             }
 
             if (is_array($value)) {
                 $html .= '<optgroup label="' . $this->escapeHtml($label)
                     . '" data-optgroup-name="' . $this->escapeHtml($optgroupName) . '">';
                 foreach ($value as $keyGroup => $optionGroup) {
                     if (!is_array($optionGroup)) {
                         $optionGroup = ['value' => $keyGroup, 'label' => $optionGroup];
                     }
                     $html .= $this->_optionToHtml($optionGroup, in_array($optionGroup['value'], $values));
                 }
                 $html .= '</optgroup>';
             } else {
                 $html .= $this->_optionToHtml(
                     ['value' => $value, 'label' => $label, 'params' => $params],
                     in_array($value, $values)
                 );
             }
         }
         $html .= '</select>';
         return $html;
     }
}