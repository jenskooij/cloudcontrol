BEGIN TRANSACTION;
CREATE TABLE `cache` (
  `path`          TEXT NOT NULL UNIQUE,
  `creationStamp` INTEGER DEFAULT 0,
  `contents`      TEXT,
  PRIMARY KEY (`path`)
);
COMMIT;