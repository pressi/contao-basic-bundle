<?php


namespace IIDO\BasicBundle\Dca;


use Contao\DataContainer;


class Container extends DataContainer
{

    public function __construct( $strTable, $strField, $strValue )
    {
        $this->strTable = $strTable;
        $this->strField = $strField;
        $this->varValue = $strValue;

        $this->strInputName = $strField;

        parent::__construct();
    }



    public function getField()
    {
        return $this->row();
    }



    /**
     * Return the name of the current palette
     *
     * @return string
     */
    public function getPalette()
    {
        // TODO: Implement getPalette() method.
    }



    /**
     * Save the current value
     *
     * @param mixed $varValue
     *
     * @throws \Exception
     */
    protected function save($varValue)
    {
        // TODO: Implement save() method.
    }
}