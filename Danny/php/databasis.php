<?php
// class voor database aanmaken
class Database {
    private static ?PDO $instance = null;
    private string $dsn;

    public function __construct(
        string $host     = 'localhost',
        string $dbName   = 'my_database',
        string $user     = 'root',
        string $password = ''
    ) {
        $this->dsn = "mysql:host=$host;dbname=$dbName;charset=utf8mb4";

        if (self::$instance === null) {
            self::$instance = new PDO($this->dsn, $user, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
    }

    public function getConnection(): PDO {
        return self::$instance;
    }
}


// ─────────────────────────────────────────
// User Model Class
// ─────────────────────────────────────────
class User {
    private int    $id;
    private string $name;
    private string $email;
    private string $createdAt;

    public function __construct(string $name, string $email, int $id = 0, string $createdAt = '') {
        $this->id        = $id;
        $this->name      = $name;
        $this->email     = $email;
        $this->createdAt = $createdAt;
    }

    // Getters
    public function getId():        int    { return $this->id; }
    public function getName():      string { return $this->name; }
    public function getEmail():     string { return $this->email; }
    public function getCreatedAt(): string { return $this->createdAt; }
}


// ─────────────────────────────────────────
// UserRepository Class (Create & Read)
// ─────────────────────────────────────────
class UserRepository {
    private PDO $pdo;

    public function __construct(Database $db) {
        $this->pdo = $db->getConnection();
        $this->createTableIfNotExists();
    }

    // Auto-create the users table if it doesn't exist
    private function createTableIfNotExists(): void {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                name       VARCHAR(100) NOT NULL,
                email      VARCHAR(150) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    // CREATE — insert a new user
    public function create(User $user): User {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, email) VALUES (:name, :email)
        ");

        $stmt->execute([
            ':name'  => $user->getName(),
            ':email' => $user->getEmail(),
        ]);

        $newId = (int) $this->pdo->lastInsertId();
        return $this->findById($newId);
    }

    // READ — find a single user by ID
    public function findById(int $id): ?User {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users WHERE id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return new User($row['name'], $row['email'], (int)$row['id'], $row['created_at']);
    }

    // READ — get all users
    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY id DESC");
        $users = [];

        foreach ($stmt->fetchAll() as $row) {
            $users[] = new User($row['name'], $row['email'], (int)$row['id'], $row['created_at']);
        }

        return $users;
    }

    // READ — find users by name (partial match)
    public function findByName(string $name): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users WHERE name LIKE :name ORDER BY name
        ");
        $stmt->execute([':name' => "%$name%"]);
        $users = [];

        foreach ($stmt->fetchAll() as $row) {
            $users[] = new User($row['name'], $row['email'], (int)$row['id'], $row['created_at']);
        }

        return $users;
    }
}


// ─────────────────────────────────────────
// Usage Example
// ─────────────────────────────────────────

// 1. Set up connection (update credentials as needed)
$db   = new Database(host: 'localhost', dbName: 'my_database', user: 'root', password: '');
$repo = new UserRepository($db);

// 2. CREATE — add new users
$alice = $repo->create(new User('Alice Smith', 'alice@example.com'));
$bob   = $repo->create(new User('Bob Jones',  'bob@example.com'));

echo "Created: #{$alice->getId()} {$alice->getName()} ({$alice->getEmail()})\n";
echo "Created: #{$bob->getId()} {$bob->getName()} ({$bob->getEmail()})\n";

// 3. READ — find by ID
$found = $repo->findById($alice->getId());
echo "\nFound by ID: {$found->getName()} — joined {$found->getCreatedAt()}\n";

// 4. READ — find all
echo "\nAll users:\n";
foreach ($repo->findAll() as $user) {
    echo "  [{$user->getId()}] {$user->getName()} <{$user->getEmail()}>\n";
}

// 5. READ — search by name
echo "\nSearch 'alice':\n";
foreach ($repo->findByName('alice') as $user) {
    echo "  {$user->getName()} <{$user->getEmail()}>\n";
}