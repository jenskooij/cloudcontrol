BEGIN TRANSACTION;
CREATE TABLE "documents_unpublished" (
  `id`                   INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `path`                 TEXT    NOT NULL UNIQUE,
  `title`                TEXT,
  `slug`                 TEXT,
  `type`                 TEXT,
  `documentType`         TEXT,
  `documentTypeSlug`     TEXT,
  `state`                TEXT,
  `lastModificationDate` INTEGER,
  `creationDate`         INTEGER,
  `publicationDate`      INTEGER,
  `lastModifiedBy`       TEXT,
  `fields`               TEXT,
  `bricks`               TEXT,
  `dynamicBricks`        TEXT
);
CREATE TABLE "documents_published" (
  `id`                   INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `path`                 TEXT    NOT NULL UNIQUE,
  `title`                TEXT,
  `slug`                 TEXT,
  `type`                 TEXT,
  `documentType`         TEXT,
  `documentTypeSlug`     TEXT,
  `state`                TEXT,
  `lastModificationDate` INTEGER,
  `creationDate`         INTEGER,
  `publicationDate`      INTEGER,
  `lastModifiedBy`       TEXT,
  `fields`               TEXT,
  `bricks`               TEXT,
  `dynamicBricks`        TEXT
);
COMMIT;
