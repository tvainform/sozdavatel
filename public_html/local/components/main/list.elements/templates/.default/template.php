<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
//echo "<pre>Template arResult: "; print_r($arResult); echo "</pre>";
?>
<table class="table table-dark">
	<thead>
	<tr>
		<th scope="col">ID</th>
		<th scope="col">Название</th>
		<th scope="col">Описание</th>
		<th scope="col">Раздел</th>
	</tr>
	</thead>
	<tbody>
	<?foreach($arResult["ITEMS"] as $items){?>
		<tr>
			<td><?=$items["ID"]?></td>
			<td><?=$items["NAME"]?></td>
			<td><?=$items["PREVIEW_TEXT"]?></td>
			<td><?=$items["SECTION"]?></td>
		</tr>
	<?}?>
	</tbody>
</table>
