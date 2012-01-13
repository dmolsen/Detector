<h3><?= (Detector::$foundIn == 'archive') ? 'Archived' : 'Your'; ?> Detector Browser Profile</h3>
<p>
	The following browser profile was created using the browser-detection of Detector. It, as well as the the following feature profile, were <strong>
	<?php
		if (Detector::$foundIn == "archive") {
			print " pulled from a profile already in the system that you asked to view. Because it's an archived profile the browser-side tests were not run.";
		} else if (Detector::$foundIn == 'cookie') {
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
			<th class="span3">User Agent:</th>
			<td><?=$ua->ua?></td>
		</tr>
		<tr>
			<th class="span3">UA Hash:</th>
			<td><?=$ua->uaHash?></td>
		</tr>
		<tr>
			<th class="span3">Gen. <? if ($ua->isMobile) { ?>OS<? } else { ?> Grouping<? } ?>:</th>
			<td><?=$ua->deviceOSGeneral?></td>
		</tr>
		<? if ($ua->isMobile) { ?>
			<tr>
				<th>Specific OS:</th>
				<td><?=$ua->deviceOSSpecific?></td>
			</tr>
		<? } ?>
		<? if ($ua->majorVersion != 0) { ?>
			<tr>
				<th>Major Version:</th>
				<td><?=$ua->majorVersion?></td>
			</tr>
			<tr>
				<th>Minor Version:</th>
				<td><?=$ua->minorVersion?></td>
			</tr>
		<? } ?>
		<tr>
			<th>Is Mobile?</th>
			<td><?=convertTF($ua->isMobile)?></td>
		</tr>
		<tr>
			<th>Is Tablet?</th>
			<td><?=convertTF($ua->isTablet)?></td>
		</tr>
		<tr>
			<th>Is Computer?</th>
			<td><?=convertTF($ua->isComputer)?></td>
		</tr>
		<tr>
			<th>Is Spider?</th>
			<td><?=convertTF($ua->isSpider)?></td>
		</tr>
	</tbody>
</table>