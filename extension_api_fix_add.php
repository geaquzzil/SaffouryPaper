<?php
$FIX_ADD_OBJECT[PR] = function (&$object) {
	if (property_exists($object, 'status') && isset($object->status)) {
		$status = $object->status;
		if (is_numeric($status)) {
			switch ($status) {
				case 0:
					$status = 'NONE';
					break;
				case 1:
					$status = 'PENDING';
					break;
				case 2:
					$status = 'RETURNED';
					break;
				case 3:
					$status = 'WASTED';
					break;
			}
			$object->status = $status;
		}
	} else {
		$object->status = "NONE";
	}
};
$FIX_ADD_OBJECT[ORDR] = function (&$object) {
	if (property_exists($object, 'status') && isset($object->status)) {
		$status = $object->status;
		if (is_numeric($status)) {
			switch ($status) {
				case 0:
					$unit = 'NONE';
					break;
				case 1:
					$unit = 'PENDING';
					break;
				case 2:
					$unit = 'PROCESSING';
					break;
				case 3:
					$unit = 'CANCELED';
					break;
				case 4:
					$unit = 'APPROVED';
					break;
			}
			$object->status = $status;
		}
	} else {
		$object->status = "NONE";
	}
};
$FIX_ADD_OBJECT[TYPE] = function (&$object) {
	if (property_exists($object, 'unit') && isset($object->unit)) {
		$unit = $object->unit;
		if (is_numeric($unit)) {
			switch ($unit) {
				case 0:
					$unit = 'KG';
					break;
				case 1:
					$unit = 'Ream';
					break;
				case 2:
					$unit = 'Sheet';
					break;
			}
			$object->unit = $unit;
		}
	} else {
		$object->unit = "KG";
	}
	if (isBase64($object->image)) {
		unset($object->delete);
		$filename_path = md5(time() . uniqid()) . ".jpg";
		$base64_string = str_replace('data:image/png;base64,', '', $object->image);
		$base64_string = str_replace(' ', '+', $object->image);
		$decoded = base64_decode($base64_string);
		file_put_contents("Images/" . $filename_path, $decoded);
		$object->image = ROOT . "Images/" . $filename_path;
	}
};
