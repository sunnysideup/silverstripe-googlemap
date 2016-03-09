<% if HasGoogleMap %>
<div class="googleMapHolder">
	<% with GoogleMapController %>

		<% if AddAddressFinder %>
	<div class="findAddressHtml">
		<a class="searchForAddress" href="#GoogleMapDiv" onclick="jQuery('#googleMapAddressFinderFieldSet').slideToggle(); return false;">search for address/location</a>
		<fieldset style="display: none;" id="googleMapAddressFinderFieldSet" class="middleColumn">
			<input type="text" class="infoTabInputAddress" id="MapAddress2" size="60" placeholder="Enter your address or location" onclick="this.value = '';"/>
			<input type="button" class="submitButton" id="InfoTabSubmitAddress2" value="find locations around your address"/>
		</fieldset>
	</div>
		<% end_if %>

		<% if CanEdit %>
	<div id="googleMapEditLine">
		<h4>How to update this map?</h4>
		<ul>
			<li><b>to add point:</b> right-mouse-click on map</li>
			<li><b>to delete point:</b> right-mouse-click on marker (icon)</li>
			<li><b>to update point:</b> left-click on a marker and drag (click on icon, move your mouse while holding the click button down, let go to drop on new location)</li>
		</ul>
	</div>
		<% end_if %>

	<% if TitleDivId %><h4 id="$TitleDivId" class="MapExtraInformation"></h4><% end_if %>

	<div id="GoogleMapDiv" style="<% if GoogleMapWidth %>width: {$GoogleMapWidth}px; <% end_if %><% if GoogleMapHeight %> height: {$GoogleMapHeight}px;<% end_if %>"></div>

	<% if DropDownDivId %><div id="$DropDownDivId" class="MapExtraInformation <% if EnoughPointsForAList %><% else %>hideMe<% end_if %>"></div><% end_if %>

	<div id="extraMapOptions" class="typography">

		<% if AllExtraLayersAsLinks %>
		<div id="GoogleMapExtraLayersAsList" class="MapExtraInformation">
			<h2>Add to the map</h2>
			<ul>
			<% loop AllExtraLayersAsLinks %>
				<li><a href="#GoogleMapDiv" onclick="return !{$MyInstanceName}.addLayer('{$Link}', '{$Title}');">$Title</a></li>
			<% end_loop %>
			</ul>
		</div>
		<% end_if %>

		<% if LayerListDivId %><div id="$LayerListDivId" class="MapExtraInformation"></div><% end_if %>

		<% if SideBarDivId %>
		<div id="$SideBarDivId" class="MapExtraInformation <% if EnoughPointsForAList %><% else %>hideMe<% end_if %>" >
			<% if ProcessedDataPointsForTemplate %>
			<ul>
				<% loop ProcessedDataPointsForTemplate %><li>$Title $AjaxInfoWindowLink</li><% end_loop %>
			</ul>
			<% end_if %>
		</div>
		<% end_if %>

		<% if DirectionsDivId %><div id="$DirectionsDivId" class="MapExtraInformation"></div><% end_if %>

		<% if StatusDivId %><div id="$StatusDivId" class="MapExtraInformation"></div><% end_if %>

	</div>
	<% end_with %>
</div>
<% end_if %>
