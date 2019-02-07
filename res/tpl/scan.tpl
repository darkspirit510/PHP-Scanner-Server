<!-- Select Region Holder -->
<div id="select"></div>

<div id="sidebar">
	<form name="scanning" action="index.php" onsubmit="return pre_scan(this);" method="POST">
		<div class="side_box">
			<h2>Scanners</h2>
			<p>
				<select name="scanner" style="width:238px;" onchange="scannerChange(this)"></select>
			</p>
			<script type="text/javascript">scanners={scanners};buildScannerOptions(scanners);setTimeout(checkScanners,5000);</script>
		</div>

		<div class="side_box" id="opt">
			<h2>Scanning Options</h2>
			<div id="source">
				<div class="label">
					<span class="tool">Source<span class="tip">Scan source (such as a document-feeder)</span></span>:
				</div>
				<div class="control">
					<div class="ie_276228">
						<select name="source" class="title" onchange="sourceChange(this)"></select>
					</div>
				</div>
			</div>

			<div class="label">
				<span class="tool">Quality<span class="tip">Resolution</span></span>:
			</div>
			
			<div class="control tool">
				<div class="ie_276228">
					<select name="quality" class="upper"></select>
				</div>
				<span class="tip">Dots Per Inch</span>
			</div>

			<div class="label">
				<span class="tool">Size<span class="tip">How big the paper is</span></span>:
			</div>
			
			<div class="control tool">
				<div class="ie_276228">
					<select name="size" onchange="paperChange(this);"></select>
				</div>
				<span class="tip"></span>
				<script type="text/javascript">paper={paper}</script>
			</div>

			<div class="label">
				<span class="tool">Orientation<span class="tip">Layout</span></span>:
			</div>
			
			<div class="control">
				<select name="ornt" onchange="layoutChange(this.value=='vert')">
					<option value="vert">Portrait</option>
					<option value="horz">Landscape</option>
				</select>
			</div>

			<div class="label">
				<span class="tool">Mode<span class="tip">Color Type</span></span>:
			</div>
			
			<div class="control">
				<div class="ie_276228">
					<select name="mode" class="title"></select>
				</div>
			</div>

			<div {raw_format}>
				<div class="label">
					<span class="tool">RAW Format<span class="tip">The format the scanner's scan is saved (pre-processing)</span></span>:
				</div>
				<div class="control">
					<select name="raw">
						<option value="pnm">pnm - Portable Any Map</option>
						<option value="tiff" {raw_scan_format}>tiff - Tagged Image File Format</option>
					</select>
				</div>
			</div>

			<div id="duplex">
				<div class="label tool">
					<span>Duplex<span class="tip">Double Sided Scan</span></span>:
				</div>
				<div class="control">
					<div class="ie_276228">
						<select name="duplex" class="title"></select>
					</div>
				</div>
			</div>

		</div>

		<div class="side_box">
			<h2>Output Options</h2>

			<div class="label">
				<span class="tool">Brightness<span class="tip">Lighting</span></span>:
			</div>
			
			<div class="control">
				<script type="text/JavaScript">{script:bright.js}</script>
			</div>

			<div class="label">
				<span class="tool">Contrast<span class="tip">Vividness</span></span>:
			</div>
			
			<div class="control">
				<script type="text/JavaScript">{script:contrast.js}</script>
			</div>

			<div class="label">
				<span class="tool">Rotate<span class="tip">Turn</span></span>:
			</div>
			
			<div class="control tool">
				<script type="text/javascript">{script:rotate.js}</script>
			</div>

			<div class="label">
				<span class="tool">Scale<span class="tip">Size/Dimensions</span></span>:
			</div>
			
			<div class="control">
				<script type="text/JavaScript">{script:scale.js}</script>
			</div>

			<div class="label">
				<span class="tool">File Type<span class="tip">Format</span></span>:
			</div>
			
			<div class="control">
				<select name="filetype" onchange="fileChange(this.value)">
					<option value="png">Portable Network Graphic: *.png</option>
					<option value="jpg">Joint Photography Group: *.jpg</option>
					<option value="tiff">Tagged Image File Format: *.tiff</option>
					<option value="txt">Text File: *.txt</option>
				</select>
			</div>

			<div style="display:none" id="lang">
				<div class="label">
					<span class="tool">Language<span class="tip">Relating to the document</span></span>:
				</div>
				
				<div class="control">
					<select name="lang">{script:langs.js}</select>
				</div>
			</div>

			<p>
				<small>Do not rotate unless this is the final scan.</small>
			</p>
		</div>

		<div class="side_box" id="sel">
			<h2>Select Region</h2>
			<p>
				<input type="hidden" name="loc_maxW"/>
				<input type="hidden" name="loc_maxH"/>
				<small>Hint: +/- can increase/decrease numbers.</small>
			</p>
			
			<div class="label">Width: </div>
			<div class="control">
				<input onkeypress="return false" type="text" readonly="readonly" name="loc_width" value="0" size="3"/> pixle(s)
			</div>
			
			<div class="label">Height: </div>
			<div class="control">
				<input onkeypress="return false" type="text" readonly="readonly" name="loc_height" value="0" size="3"/> pixle(s)
			</div>
			
			<div class="label">X<sub>1</sub> (Left):</div>
			<div class="control">
				<input onkeypress="return validateKey(this,event,ias);" onchange="this.value=Number(this.value)||0;" type="text" name="loc_x1" value="0" size="3"/> pixle(s)
			</div>
			
			<div class="label">Y<sub>1</sub> (Top):</div>
			<div class="control">
				<input onkeypress="return validateKey(this,event,ias);" onchange="this.value=Number(this.value)||0;" type="text" name="loc_y1" value="0" size="3"/> pixle(s)
			</div>
			
			<div class="label">X<sub>2</sub> (Right):</div>
			<div class="control">
				<input onkeypress="return validateKey(this,event,ias);" onchange="this.value=Number(this.value)||0;" type="text" name="loc_x2" value="0" size="3"/> pixle(s)
			</div>
			
			<div class="label">Y<sub>2</sub> (Bottom):</div>
			<div class="control">
				<input onkeypress="return validateKey(this,event,ias);" onchange="this.value=Number(this.value)||0;" type="text" name="loc_y2" value="0" size="3"/> pixle(s),
			</div>
			
			<p class="center">
				<input type="button" value="Update" onclick="setRegion(ias);"/>
				<input type="button" onclick="clearRegion(ias,true)" value="Clear"/>
			</p>
		</div>

		<div class="side_box">
			<h2>Scan Image</h2>
			<p class="center">
				<input type="hidden" name="page" value="Scan"/>
				<input type="submit" value="Scan Image" name="action">
				<input name="reset" type="reset" value="Reset Options" onclick="clearRegion(ias,false);setTimeout(scanReset,1);"/>
			</p>
		</div>

		<div class="side_box">
			<h2>Storage Target</h2>
			<p class="center">
				<select name=''>
					<option value='default'>Default directory</option>
					{nextcloud_users}
				</select>
			</p>
		</div>

		<div class="side_box">
			<h2>Settings</h2>
			<p>
				<input name="set_save" type="text" size="11" onkeypress="if(event.which==13||event.keyCode==13){document.scanning.saveas.click();return false;}"/>
				<input onclick="if(document.scanning.set_save.value==''){return false;}" type="submit" name="saveas" value="Save"/>
				<input type="button" value="Set Default" onclick="setDefault(document.scanning)"/>
			</p>
			<p class="center">{???1}</p>
		</div>
	</form>
</div>

<!-- Preview Pane -->
<div id="preview" class="box-wide">
	<div id="preview_links">
		<h2>Preview Pane</h2>
		<p>{???2}</p>
	</div>
	
	<div id="preview_img">
		<p>
			<img src="res/images/blank.gif" title="Preview" alt="Preview"/>
			<img alt="Scanning" src="res/images/blank.gif" title="Scanning" style="z-index:-1;"/>
		</p>
	</div>
</div>
