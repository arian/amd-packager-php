<!doctype html>
<html>
<head>
	<meta charset="utf-8">

	<title>AMD Packager Web</title>

	<link rel="stylesheet" href="<?php echo $BASE_PATH; ?>/assets/reset.css">
	<link rel="stylesheet" href="<?php echo $BASE_PATH; ?>/assets/packager.css">

	<script src="https://ajax.googleapis.com/ajax/libs/mootools/1.3.2/mootools-nocompat-yui-compressed.js"></script>
	<script src="<?php echo $BASE_PATH; ?>/assets/packager.js"></script>
	<script type="text/javascript">document.addEvent('domready', Packager.init);</script>

</head>

<body>

	<form id="packager" action="<?php echo $BASE_PATH; ?>/" method="POST">

		<?php foreach ($packages as $package => $modules): ?>

		<div id="package-<?php echo $package; ?>" class="package">

			<table class="vertical">
				<thead>
					<tr class="first last">
						<th>Name</th>
						<td>
							<?php echo $package; ?>
							<div class="buttons">
								<input type="hidden" name="disabled[]" class="toggle" value="" />
								<div class="enabled">
									<input type="button" class="select" value="select package" />
									<input type="button" class="deselect" value="deselect package" />
									<input type="button" class="disable" value="disable package" />
								</div>
								<div class="disabled">
									<input type="button" class="enable" value="enable package" />
								</div>
							</div>
						</td>
					</tr>
				</thead>
			</table>

			<table class="horizontal">
				<tr class="first">
					<th class="first"></th>
					<th class="last">File</th>
				</tr>

			<?php foreach ($modules as $id => $module): ?>

				<tr class="middle unchecked">
					<td class="first check">
						<div class="checkbox"></div>
						<input type="checkbox" name="modules[]" value="<?php echo $id; ?>" data-depends="<?php echo implode(', ', $module['dependencies']); ?>">
					</td>
					<td class="last file"><?php echo $module['id']; ?></td>
				</tr>

			<?php endforeach; ?>

			</table>

		</div>
		
		<?php endforeach; ?>

		<div class="options">

			<table class="vertical">
				<thead>
					<tr class="first last">
						<th>Build Options</th>
					</tr>
				</thead>
			</table>

			<table class="horizontal">
				<tr>
					<th colspan="2" class="first last">Compression</th>
				</tr>

				<tr class="middle checked selected">
					<td class="first check">
						<div class="radio"></div>
						<input type="radio" name="compressor" value="no" checked>
					</td>
					<td class="last">No Compression</td>
				</tr>
				<?php if (!empty($options['minifier']['yui'])): ?>
				<tr class="middle unchecked">
					<td class="first check">
						<div class="radio"></div>
						<input type="radio" name="compressor" value="yui">
					</td>
					<td class="last">YUI Compressor</td>
				</tr>
				<?php endif; ?>
				<?php if (!empty($options['minifier']['uglify-js'])): ?>
				<tr class="middle unchecked">
					<td class="first check">
						<div class="radio"></div>
						<input type="radio" name="compressor" value="ugly">
					</td>
					<td class="last">Uglify JS</td>
				</tr>
				<?php endif; ?>

				<tr>
					<th colspan="2" class="first last">Concatenate</th>
				</tr>

				<tr class="middle checked selected">
					<td class="first check">
						<div class="radio"></div>
						<input type="radio" name="concatenation" value="single" checked>
					</td>
					<td class="last">As single file</td>
				</tr>
				<tr class="last unchecked">
					<td class="first check">
						<div class="radio"></div>
						<input type="radio" name="concatenation" value="package">
					</td>
					<td class="last">A file per package</td>
				</tr>
			</table>

		</div>

		<p class="submit">
			<input type="reset" value="reset">
			<input type="submit" value="download">
		</p>

	</form>



</body>
</html>
