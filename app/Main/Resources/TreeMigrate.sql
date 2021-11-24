CREATE TABLE IF NOT EXISTS `migrate_data`
(
    `id`         int(11)     NOT NULL AUTO_INCREMENT,
    `entity`     varchar(64) NOT NULL,
    `old_id`     int(11)     NULL,
    `new_id`     int(11)     NULL,
    `date_added` datetime    NOT NULL,
    PRIMARY KEY (`id`),
    INDEX (`entity`, `old_id`, `new_id`, `date_added`)
);

DROP PROCEDURE IF EXISTS `tree_migrate_procedure`;

DELIMITER //
CREATE PROCEDURE `tree_migrate_procedure`()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION, NOT FOUND, SQLWARNING BEGIN
        ROLLBACK;
        GET DIAGNOSTICS CONDITION 1 @`errno` = MYSQL_ERRNO, @`sqlstate` = RETURNED_SQLSTATE, @`text` = MESSAGE_TEXT;
        SET @full_error = CONCAT('ERROR ', @`errno`, ' (', @`sqlstate`, '): ', @`text`);
        SELECT @track_no, @full_error;
    END;

    START TRANSACTION;

    SET @track_no = 0;
    SET @path_to_tree = '.';
    SET @tree_parent_id = NULL;##$new_server_tree_id
    SET @last_tree_id = 0;
    SET @new_id = 0;
    SET @message = '';
    SET @date_added = NOW();

    IF @tree_parent_id IS NOT NULL THEN
        SET @path_to_tree = ( SELECT CONCAT(`path`, '.', `id`) AS path_to_tree FROM tree WHERE `id` = @tree_parent_id );
    END IF;

    IF @path_to_tree IS NULL THEN
        SET @message = 'Path not found!';
        ROLLBACK;
    ELSE
        SET FOREIGN_KEY_CHECKS = 0;

        ##SQL##


        SET @track_no = @track_no + 1;

        UPDATE devices AS t1 INNER JOIN ( SELECT CONCAT('', new_id) AS parent, CONCAT(old_id) AS old_id
                                          FROM migrate_data
                                          WHERE entity = 'tree'
                                            AND date_added = @date_added ) AS t2
        SET t1.parent = t2.parent
        WHERE t1.parent = t2.old_id;

        SET @track_no = @track_no + 1;

        UPDATE registrators AS r INNER JOIN ( SELECT *
                                              FROM migrate_data
                                              WHERE migrate_data.entity = 'devices' AND date_added = @date_added ) AS d
        SET r.device_id = d.new_id
        WHERE d.old_id = r.device_id;

        SET @track_no = @track_no + 1;

        INSERT INTO modems_devices_rel
        SELECT t1.modem_id, t1.id
        FROM devices t1
                 INNER JOIN ( SELECT new_id FROM migrate_data WHERE entity = 'devices' AND date_added = @date_added ) t2
                            ON t1.id = t2.new_id AND t1.modem_id IS NOT NULL
        ON DUPLICATE KEY UPDATE modems_devices_rel.device_id = t1.id, modems_devices_rel.modem_id = t1.modem_id;

        INSERT INTO devices_registrators_rel
        SELECT device_id, id
        FROM registrators
        WHERE device_id IN ( SELECT new_id FROM migrate_data WHERE entity = 'devices' AND date_added = @date_added );

        SET @track_no = @track_no + 1;

        INSERT INTO devices_registrators_rel
        SELECT device_id, id
        FROM registrators
        WHERE device_id IN ( SELECT new_id FROM migrate_data WHERE entity = 'devices' AND date_added = @date_added );


        SET @track_no = @track_no + 1;

        SET FOREIGN_KEY_CHECKS = 1;
        SET @message = 'Successfully executed!';
    END IF;

    SELECT @track_no, @message; COMMIT;
END //
DELIMITER ;

CALL tree_migrate_procedure();
DROP PROCEDURE IF EXISTS `tree_migrate_procedure`;
