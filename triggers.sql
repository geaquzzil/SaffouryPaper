
BEGIN 
IF OLD.fromWarehouse <> NEW.fromWarehouse
OR OLD.toWarehouse <> NEW.toWarehouse THEN
DECLARE done INT DEFAULT FALSE;
DECLARE p INT;
DECLARE q DOUBLE;
DECLARE cur CURSOR FOR SELECT ProductID,quantity FROM transfers_details WHERE transfers_details.TransferID = OLD.iD;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
OPEN cur;
ins_loop: LOOP 
FETCH cur INTO p,q;
IF done THEN LEAVE ins_loop;
END IF;
CALL PlusQuantity(OLD.fromWarehouse, p, q, -1);
CALL MinusQuantity(NEW.fromWarehouse, p, q, -1);
CALL MinusQuantity(OLD.toWarehouse, p, q, -1);
CALL PlusQuantity(NEW.toWarehouse, p, q, -1);
END LOOP;
CLOSE cur;
END IF;
END;