-- Schéma SQLite versionné — utilisé pour le test d'intégration du repository.
-- Le test boote le kernel Symfony, qui se connecte à database/data.db
-- (initialisé par `make init-db` ou `composer init-db`).

CREATE TABLE IF NOT EXISTS orders (
    id        INTEGER     NOT NULL PRIMARY KEY,
    customer  VARCHAR(255) NOT NULL,
    total     FLOAT        NOT NULL,
    status    VARCHAR(32)  NOT NULL
);

INSERT INTO orders (id, customer, total, status) VALUES
    (1, 'John',  100.00, 'CONFIRMED'),
    (2, 'Alice', 250.00, 'PENDING'),
    (3, 'Bob',   175.50, 'CONFIRMED');
