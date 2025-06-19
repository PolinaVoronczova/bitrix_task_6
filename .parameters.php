<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
/** @var array $arCurrentValues */

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock'))
{
	return;
}

$arTypesEx = CIBlockParameters::GetIBlockTypes();

$arIBlocks = [];

$db_iblock = CIBlock::GetList(["SORT"=>"ASC"], $iblockFilter);
while($arRes = $db_iblock->Fetch())
{
	$arIBlocks[$arRes["ID"]] = "[" . $arRes["ID"] . "] " . $arRes["NAME"];
}

$arComponentParameters = [
	"GROUPS" => [],
	"PARAMETERS" => [
		"AJAX_MODE" => [],
		"IBLOCK_TYPE" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arTypesEx,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		],
		"IBLOCK_ID" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => '={$_REQUEST["ID"]}',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		],
		"FILTER_NAME" => [
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_FILTER"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		],
		"FIELD_CODE" => CIBlockParameters::GetFieldCode(GetMessage("IBLOCK_FIELD"), "DATA_SOURCE"),
	],
];

CIBlockParameters::Add404Settings($arComponentParameters, $arCurrentValues);
