<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
class IblockListComponent extends CBitrixComponent
{
    /**
     * Подготавливаем входные параметры
     *
     * @param array $arParams
     *
     * @return array
     */
    public function onPrepareComponentParams($arParams)
    {
        $arParams['USER_ID'] ??= 0;
        $arParams['FILTER'] =  $GLOBALS[$arParams['FILTER_NAME']] ?: [];
        $arParams['IBLOCK_ID'] = (int)$arParams['IBLOCK_ID'] ?: null;

        return $arParams;
    }
    /**
     * Основной метод выполнения компонента
     *
     * @return void
     */
    public function executeComponent()
    {
        // Кешируем результат, чтобы не делать постоянные запросы к базе
        if ($this->startResultCache())
        {
            $this->initResult();
            
            // Если ничего не найдено, отменяем кеширование
            if (empty($this->arResult))
            {
                $this->abortResultCache();
                ShowError('Информация не найдена.');
                
                return;
            }
            
            $this->includeComponentTemplate();
        }
    }
    /**
     * Инициализируем результат
     *
     * @return void
     */
    private function initResult(): void
    {
        Loader::includeModule("iblock");

        $iblockType = $this->arParams['IBLOCK_TYPE'];
        $iblockId = $this->arParams['IBLOCK_ID'];

        $arrIdOrType = 
        [
            'TYPE_ID' => 'TYPE',
            'VALUE' => $iblockType
        ];

        if ($iblockId) {
            $arrIdOrType = 
            [
                'TYPE_ID' => 'IBLOCK_ID',
                'VALUE' => $iblockId
            ];
        }
        
        $this->setResult($arrIdOrType);
    }

    private function setResult(array $arrIdOrType)
    {
        $arFilter = $this->arParams['FILTER'];
        $arFilter[$arrIdOrType['TYPE_ID']] = $arrIdOrType['VALUE'];
        $arFilter['ACTIVE'] = 'Y';

        $arSelect = [
            'ID',
            'NAME',
            'IBLOCK_ID'
        ];

        $rsElements = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $arFilter,
            false,
            false,
            $arSelect
        );

        while ($arElement = $rsElements->fetch()) {
            $arButtons = CIBlock::GetPanelButtons(
                $arElement["IBLOCK_ID"],
                $arElement["ID"],
                0,
                array("SECTION_BUTTONS" => false, "SESSID" => false)
            );
           
            $editLink = $arButtons["edit"]["edit_element"]["ACTION_URL"] ?? '';
            $deleteLink = $arButtons["edit"]["delete_element"]["ACTION_URL"] ?? '';
 
            $this->arResult['ITEMS'][$arElement['IBLOCK_ID']][] = [
                'ID' => $arElement['ID'],
                'NAME' => $arElement['NAME'],
                'DATE' => $arElement['DATE_CREATE'],
                'IBLOCK_ID' => $arElement['IBLOCK_ID'],
                'EDIT_LINK' => $editLink,
                'DELETE_LINK' => $deleteLink
            ];  
        }
    }
}
?>