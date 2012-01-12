<h3>Detector's Feature Profile for You</h3>
<p>
	The following feature profile was primarily created using <a href="http://www.modernizr.com/docs/#s2">Modernizr's core tests</a>. The left column, <strong>Browser</strong>, is populated by JavaScript using a copy of Modernizr that is loaded with this page. The right column, <strong>Server</strong>, is populated by PHP using the profile created by Detector for your browser.
	In addition to the core tests
	I've added an extended test that checks for emoji support as well as a per request test to check the device pixel ratio. Both were added using the <a href="http://www.modernizr.com/docs/#addtest">Modernizr.addTest() Plugin API</a>.
	To learn more about core, extended, and per request tests please <a href="https://github.com/dmolsen/Detector">review the README</a>.  To access any of these options in your PHP app you'd simply type <code>$ua->featureName</code>.
</p>
<table class="zebra-striped span9">
	<thead>
		<tr>
			<th>Features</th>
			<th>Browser</th>
			<th>Server</th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach($ua as $key => $value) {
				if (!preg_match("/(ua|uaHash|deviceOSGeneral|deviceOSSpecific|majorVersion|minorVersion|isMobile|isTablet|isComputer|isSpider)/",$key)) {
					if (is_object($value)) {
						foreach ($value as $vkey => $vvalue) { ?>
							<tr>
								<th class="span7"><?=$key?>-><?=$vkey?>:</th>
								<td class="span1"><script type="text/javascript">if (Modernizr['<?=$key?>']['<?=$vkey?>']) { document.write("<span class='label success'>true</span>"); } else { document.write("<span class='label important'>false</span>"); }</script></td>
								<td class="span1"><?=convertTF($vvalue)?></td>
							</tr>
						<?php }
						$jsonTemplateCore->$key = $value;
					} else { ?>
						<tr>
							<? if (preg_match("/(desktop|mobile|tablet)/",$key)) { ?>
								<th class="span7"><?=$key?>: <small><em>(via media queries)</em></small></th>
							<? } else { ?>
								<th class="span7"><?=$key?>:</th>
							<? } ?>
							<? if (!preg_match("/(desktop|mobile|tablet|colordepth|json|overflowscrolling|emoji|hirescapable)/",$key)) { ?>
								<td class="span1"><script type="text/javascript">if (Modernizr['<?=$key?>']) { document.write("<span class='label success'>true</span>"); } else { document.write("<span class='label important'>false</span>"); }</script></td>
							<? } else { ?>
								<td class="span1"><span class='label'>N/A</span></td>
							<? } ?>
							<td class="span1"><?=convertTF($value)?></td>
						</tr>
					<?php }
				}
			}
		?>
	</tbody>
</table>
<p>
	Please note, the only reason why a full slate of Modernizr tests is always done with this demo is for the feature profile comparison. You can include as much or as little of Modernizr as you want on your site. You can even leave it out entirely.
</p>