<h3><?= (Detector::$foundIn == 'archive') ? 'Archived' : 'Your'; ?> Detector Browser Profile</h3>
<p>
	The following browser profile was created using <a href="https://github.com/dmolsen/ua-parser-php">ua-parser-php</a>. This information
	is derived solely from the user agent string for your browser.
</p>
<? 
	if (Detector::$foundIn == 'archive') {
		if ($uaListJSON = @file_get_contents(__DIR__."/../../lib/Detector/user-agents/core/ua.list.json")) {
			$uaList = (array) json_decode($uaListJSON);
			asort($uaList);
			$i = 0;
			$oldkey = '';
			$next = '';
			foreach($uaList as $key => $value) {
				if ($i == 1) {
					$next = $key;
					break;
				}
				if ($key == $ua->uaHash) {
					$previous = $oldkey;
					$i = 1;
				}
				$oldkey = $key;
			}

		}
	}
?>

<table class="zebra-striped span9">
	<thead>
		<tr>
			<th colspan="2">Browser Properties <span style="float: right; font-weight: normal; font-size: 12px;">
			<? if ($previous != '') { ?>
				<a href="/?pid=<?=$previous?>">Previous Profile</a>
			<? } ?>
			<? if (($next != '') && ($previous != '')) { ?>
				|
			<? } ?>
			<? if ($next != '') { ?>
				<a href="/?pid=<?=$next?>">Next Profile</a>
			<? } ?></span></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th class="span3">User Agent:</th>
			<td><?=$ua->ua?></td>
		</tr>
		<?php
			if ($ua->isMobile && (Detector::$foundIn != "archive")) {
				
			} else { ?>
				<tr>
					<th class="span3">UA Hash:</th>
					<td><?=$ua->uaHash?></td>
				</tr>
		<?php } ?>
		<? if (isset($ua->full)) { ?>
			<tr>
				<th class="span3">Browser/OS:</th>
				<td><?=$ua->full?></td>
			</tr>
		<? } else { ?>
			<tr>
				<th class="span3">Browser:</th>
				<td><?=$ua->browserFull?></td>
			</tr>
		<? } ?>
		<? if ($ua->device != '') { ?>
			<tr>
				<th>Device:</th>
				<td><?=$ua->deviceFull?></td>
			</tr>
		<? } ?>
		<? if ($ua->browser == 'Mobile Safari') { ?>
			<tr>
				<th>Is UIWebview?</th>
				<td><?=convertTF($ua->isUIWebview)?></td>
			</tr>
		<? } ?>
		<tr>
			<th>Is Mobile?</th>
			<td><?=convertTF($ua->isMobile)?></td>
		</tr>
		<tr>
			<th>Is Mobile Device?</th>
			<td><?=convertTF($ua->isMobileDevice)?></td>
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

<p>
	<strong>Something wrong with this profile?</strong> Please, <a href="contact.php?cid=<?=$ua->uaHash?>">let me know</a>. Note that
	the <strong>"tablet" classification may be incorrect</strong> for those Android tablets using an OS older than Android 3.0.
</p>