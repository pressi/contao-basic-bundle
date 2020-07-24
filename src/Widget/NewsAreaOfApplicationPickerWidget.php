<?php
/*******************************************************************
 * (c) 2020 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\BasicBundle\Widget;

use IIDO\BasicBundle\Model\NewsAreaOfApplicationModel;
use Contao\Database;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;

class NewsAreaOfApplicationPickerWidget extends Widget
{
    /**
     * Submit user input.
     *
     * @var bool
     */
    protected $blnSubmitInput = true;



    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'be_widget';


    /**
     * Table name.
     *
     * @var string
     */
    protected $strTable = 'tl_news_areaOfApplication';



    /**
     * Generate the widget and return it as string.
     *
     * @return string
     */
    public function generate()
    {
        $values = [];

        // Can be an array
        if (!empty($this->varValue) && null !== ($entries = NewsAreaOfApplicationModel::findMultipleByIds((array) $this->varValue)))
        {
            /** @var NewsAreaOfApplicationModel $entry */
            foreach ($entries as $entry)
            {
                $values[$entry->id] = Image::getHtml('iconPLAIN.svg').' '.$entry->title;
            }
        }

        $return = '<input type="hidden" name="'.$this->strName.'" id="ctrl_'.$this->strId.'" value="'.implode(',', array_keys($values)).'">'.($this->sorting ? '
  <input type="hidden" name="'.$this->strOrderName.'" id="ctrl_'.$this->strOrderId.'" value="'.$this->{$this->orderField}.'">' : '').'
  <div class="selector_container">'.(($this->sorting && count($values) > 1) ? '
    <p class="sort_hint">'.$GLOBALS['TL_LANG']['MSC']['dragItemsHint'].'</p>' : '').'
    <ul id="sort_'.$this->strId.'" class="'.($this->sorting ? 'sortable' : '').'">';

        foreach ($values as $k => $v)
        {
            $return .= '<li data-id="'.$k.'">'.$v.'</li>';
        }

        $return .= '</ul>';
        $pickerBuilder = System::getContainer()->get('contao.picker.builder');

        if (!$pickerBuilder->supportsContext('newsAreaOfApplication'))
        {
            $return .= '
	<p><button class="tl_submit" disabled>'.$GLOBALS['TL_LANG']['MSC']['changeSelection'].'</button></p>';
        }
        else
        {
            $extras = ['fieldType' => $this->fieldType];

            if (is_array($this->rootNodes))
            {
                $extras['rootNodes'] = array_values($this->rootNodes);
            }

            $return .= '
	<p><a href="'.ampersand($pickerBuilder->getUrl('newsAreaOfApplication', $extras)).'" class="tl_submit" id="pt_'.$this->strName.'">'.$GLOBALS['TL_LANG']['MSC']['changeSelection'].'</a></p>
	<script>
	  $("pt_'.$this->strName.'").addEvent("click", function(e) {
		e.preventDefault();
		Backend.openModalSelector({
		  "id": "tl_listing",
		  "title": "'.StringUtil::specialchars(str_replace("'", "\\'", $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['label'][0])).'",
		  "url": this.href + document.getElementById("ctrl_'.$this->strId.'").value,
		  "callback": function(table, value) {
			new Request.Contao({
			  evalScripts: false,
			  onSuccess: function(txt, json) {
				$("ctrl_'.$this->strId.'").getParent("div").set("html", json.content);
				json.javascript && Browser.exec(json.javascript);
			  }
			}).post({"action":"reloadNewsAreasOfApplicationWidget", "name":"'.$this->strId.'", "value":value.join("\t"), "REQUEST_TOKEN":"'.REQUEST_TOKEN.'"});
		  }
		});
	  });
	</script>'.($this->sorting ? '
	<script>Backend.makeMultiSrcSortable("sort_'.$this->strId.'", "ctrl_'.$this->strId.'", "ctrl_'.$this->strId.'")</script>' : '');
        }

        $return = '<div>'.$return.'</div></div>';

        return $return;
    }



    /**
     * Return an array if the "multiple" attribute is set.
     *
     * @param mixed $input
     *
     * @return mixed
     */
    protected function validator($input)
    {
        $this->checkValue($input);

        if ($this->hasErrors()) {
            return '';
        }

        if (!$input) {
            if ($this->mandatory) {
                $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
            }

            return '';
        } elseif (false === strpos($input, ',')) {
            return $this->multiple ? [(int) $input] : (int) $input;
        }
        $arrValue = array_map('intval', array_filter(explode(',', $input)));

        return $this->multiple ? $arrValue : $arrValue[0];
    }



    /**
     * Check the selected value.
     *
     * @param string $input
     */
    protected function checkValue($input)
    {
        if ('' === $input || !is_array($this->rootNodes)) {
            return;
        }

        if (false === strpos($input, ',')) {
            $ids = [(int) $input];
        } else {
            $ids = array_map('intval', array_filter(explode(',', $input)));
        }

        if (count(array_diff($ids, array_merge($this->rootNodes, Database::getInstance()->getChildRecords($this->rootNodes, $this->strTable)))) > 0)
        {
            $this->addError($GLOBALS['TL_LANG']['ERR']['invalidPages']);
        }
    }
}
