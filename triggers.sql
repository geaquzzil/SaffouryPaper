BEGIN
IF OLD.isDirect !=0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Jouranl cannot be deleted.';
END IF;
END


INSERT INTO `spendings` (`iD`, `CashBoxID`, `EqualitiesID`, `EmployeeID`, `NameID`, `isDirect`, `date`, `value`, `fromBox`, `comments`) VALUES ('37', '1', '157', '1', '16', NULL, current_timestamp(), '322000', '0', NULL);
INSERT INTO `incomes` (`iD`, `CashBoxID`, `EqualitiesID`, `EmployeeID`, `NameID`, `isDirect`, `date`, `value`, `fromBox`, `comments`) VALUES ('53', '1', '158', '1', '16', NULL, current_timestamp(), '100', '0', '100 $ مقابل: 322,000 ل.س سعر الصرف: 3220\"');
