<?php
function paymentAdds($origianlObject, $object, $tableNameToAddOn)
{
	$payDollar = isSetKeyFromObjReturnValue($origianlObject, CRED . 'Dollar');
	$paySYP = isSetKeyFromObjReturnValue($origianlObject, CRED . 'SYP');

	if (!is_null($paySYP)) {
		$paySYP[KCUST] = $object[KCUST];
		addEditObject($paySYP, $tableNameToAddOn, getDefaultAddOptions());
	}
	if (!is_null($payDollar)) {
		$payDollar[KCUST] = $object[KCUST];
		addEditObject($payDollar, $tableNameToAddOn, getDefaultAddOptions());
	}
}
$AFTER_ADD_OBJ[PURCH] = function ($origianlObject, $object) {
	paymentAdds($origianlObject, $object, DEBT);
};
$AFTER_ADD_OBJ[ORDR] = function ($origianlObject, $object) {
	paymentAdds($origianlObject, $object, CRED);

	///If there are any cut request then add it 		
	$cutRequest = isSetKeyFromObjReturnValue($origianlObject, CUT);
};
