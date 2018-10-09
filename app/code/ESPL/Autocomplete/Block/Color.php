<?php
/**
 * ESPL_Autocomplete extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Elitech Rest API License
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.elitechsystems.com/license.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@elitechsystems.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * versions in the future. If you wish to customize this extension for your
 * needs please refer to http://www.elitechsystems.com for more information.
 *
 * @category     Elitech
 * @package      ESPL_Autocomplete
 * @author-email info@elitechsystems.com
 * @copyright    Copyright 2017 � elitechsystems.com. All Rights Reserved
 */

namespace ESPL\Autocomplete\Block;

class Color extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
    @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
    Input  : add color picker in admin configuration fields
    Output : return string script
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = $element->getData('value');
        $html .= '<script type="text/javascript">
            require(["jquery","espl/colorpicker"], function ($) {
                $(document).ready(function () {
                    var $el = $("#'.$element->getHtmlId().'");
                    $el.css("backgroundColor", "'.$value.'");

                        // Attach the color picker
                    $el.ColorPicker({
                        color: "'.$value.'",
                        onChange: function (hsb, hex, rgb) {
                            $el.css("backgroundColor", "#" + hex).val("#" + hex);
                        }
                    });
                });
            });
        </script>';

        return $html;
    }
}
