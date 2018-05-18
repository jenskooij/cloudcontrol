BEGIN TRANSACTION;
CREATE TABLE `term_count` (
  `documentPath` TEXT,
  `field`        TEXT,
  `term`         TEXT,
  `count`        INTEGER
);
CREATE TABLE `term_frequency` (
  `documentPath` TEXT,
  `field`        TEXT,
  `term`         TEXT,
  `frequency`    NUMERIC,
  `termNorm`     NUMERIC
);
CREATE TABLE `inverse_document_frequency` (
  `term`                     TEXT,
  `inverseDocumentFrequency` NUMERIC
);
CREATE TABLE "search_analysis" (
  `query`       TEXT    DEFAULT "",
  `sessionId`   TEXT    DEFAULT "",
  `timestamp`   INTEGER DEFAULT 0,
  `requestUri`  TEXT    DEFAULT "",
  `conversion`  TEXT    DEFAULT "",
  `resultCount` INTEGER DEFAULT 0
);
COMMIT;