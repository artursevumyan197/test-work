
    CREATE TABLE IF NOT EXISTS url (
        id INT( 11 ) AUTO_INCREMENT ,
        url VARCHAR( 255 ) NOT NULL DEFAULT '' ,
        hash VARCHAR( 14 ) NOT NULL DEFAULT '' ,
        created_date DATETIME NOT NULL,
        sent BOOLEAN NOT NULL DEFAULT 0,
        domain VARCHAR(255) NULL,
        PRIMARY KEY ( id )
    ) ;
