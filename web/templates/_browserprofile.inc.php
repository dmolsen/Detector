<h3>Detector's Browser Profile for You</h3>
<p>
	The following browser profile was created using the browser-detection of Detector. It, as well as the the following feature profile, were <strong>
	<?php
		if (Detector::$foundIn == 'cookie') {
			print " created when you first hit this page because Detector didn't recognize your user-agent. You may have experienced a very brief redirect when loading the page initially. The profiles have now been saved for use with other visitors.";
		} else if (Detector::$foundIn == 'file') {
			print " created in the past when another user with the same user agent visited this demo. Detector simply pulled the already existing information for your visit.";
		} else {
			print " pulled from session because you've visited this page before.";
		}
	?></strong>
</p>
<table class="zebra-striped span9">
	<thead>
		<tr>
			<th colspan="2">Browser Properties</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="span3">User Agent:</td>
			<td><?=$ua->ua?></td>
		</tr>
		<tr>
			<td class="span3">Gen. <? if ($ua->isMobile) { ?>OS<? } else { ?> Grouping<? } ?>:</td>
			<td><?=$ua->deviceOSGeneral?></td>
		</tr>
		<? if ($ua->isMobile) { ?>
			<tr>
				<td>Specific OS:</td>
				<td><?=$ua->deviceOSSpecific?></td>
			</tr>
		<? } ?>
		<? if ($ua->majorVersion != 0) { ?>
			<tr>
				<td>Major Version:</td>
				<td><?=$ua->majorVersion?></td>
			</tr>
			<tr>
				<td>Minor Version:</td>
				<td><?=$ua->minorVersion?></td>
			</tr>
		<? } ?>
		<tr>
			<td>Is Mobile?</td>
			<td><?=convertTF($ua->isMobile)?></td>
		</tr>
		<tr>
			<td>Is Tablet?</td>
			<td><?=convertTF($ua->isTablet)?></td>
		</tr>
		<tr>
			<td>Is Computer?</td>
			<td><?=convertTF($ua->isComputer)?></td>
		</tr>
		<tr>
			<td>Is Spider?</td>
			<td><?=convertTF($ua->isSpider)?></td>
		</tr>
	</tbody>
</table>