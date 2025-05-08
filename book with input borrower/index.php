<?php
session_start();

class Book {
    private $id;
    private $title;
    private $author;
    private $quantity;

    public function __construct($id, $title, $author, $quantity) {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->quantity = $quantity;
    }

    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getAuthor() { return $this->author; }
    public function getQuantity() { return $this->quantity; }
    public function decreaseQuantity() { $this->quantity--; }
    public function setTitle($title) { $this->title = $title; }
    public function setAuthor($author) { $this->author = $author; }
    public function setQuantity($quantity) { $this->quantity = $quantity; }
    public function increaseQuantity() { $this->quantity++; }
}

class Transaction {
    private $transactionId;
    private $bookTitle;
    private $borrower;

    public function __construct($transactionId, $bookTitle, $borrower) {
        $this->transactionId = $transactionId;
        $this->bookTitle = $bookTitle;
        $this->borrower = $borrower;
    }

    public function getTransactionId() { return $this->transactionId; }
    public function getBookTitle() { return $this->bookTitle; }
    public function getBorrower() { return $this->borrower; }
}

// Initialize session variables if not set
if (!isset($_SESSION['books'])) {
    $_SESSION['books'] = array(
        new Book(1, "JavaScript for Beginners", "John Doe", 3),
        new Book(2, "HTML & CSS Design", "Jane Smith", 5),
        new Book(3, "Mastering Java", "David Johnson", 2),
        new Book(4, "Python Crash Course", "Eric Matthews", 4),
        new Book(5, "Python Crash Course", "Eric Matthews", 4)
    );
}

if (!isset($_SESSION['transactions'])) {
    $_SESSION['transactions'] = array();
}

if (!isset($_SESSION['transactionCounter'])) {
    $_SESSION['transactionCounter'] = 1;
}

if (!isset($_SESSION['nextBookId'])) {
    $_SESSION['nextBookId'] = 5;
}

// Handle adding new book
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $txtTitle = isset($_POST['txtTitle']) ? $_POST['txtTitle'] : '';
    $txtAuthor = isset($_POST['txtAuthor']) ? $_POST['txtAuthor'] : '';
    $txtQuantity = isset($_POST['txtQuantity']) ? $_POST['txtQuantity'] : '';
    
    if (!empty($txtTitle) && !empty($txtAuthor) && !empty($txtQuantity)) {
        $newBook = new Book(
            $_SESSION['nextBookId']++,
            $txtTitle,
            $txtAuthor,
            (int)$txtQuantity
        );
        $_SESSION['books'][] = $newBook;
        $_SESSION['update_success'] = "Book Added Successfully";
    }
    header("Location: index.php");
    exit();
}

// Handle book borrowing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['borrow'])) {
    $bookIndex = $_POST['bookIndex'];
    $txtBorrower = isset($_POST['txtBorrower']) ? $_POST['txtBorrower'] : '';
    
    if (isset($_SESSION['books'][$bookIndex]) && $_SESSION['books'][$bookIndex]->getQuantity() > 0 && !empty($txtBorrower)) {
        $_SESSION['books'][$bookIndex]->decreaseQuantity();
        $newTransaction = new Transaction(
            $_SESSION['transactionCounter']++,
            $_SESSION['books'][$bookIndex]->getTitle(),
            $txtBorrower
        );
        $_SESSION['transactions'][] = $newTransaction;
        $_SESSION['update_success'] = "Book borrowed Successfully";
    }
}

// Handle book editing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'edit' && isset($_POST['bookIndex'])) {
        $index = $_POST['bookIndex'];
        if (isset($_SESSION['books'][$index])) {
            $_SESSION['books'][$index]->setTitle($_POST['txtTitle']);
            $_SESSION['books'][$index]->setAuthor($_POST['txtAuthor']);
            $_SESSION['books'][$index]->setQuantity($_POST['txtQuantity']);
            $_SESSION['update_success'] = "Book edit Successfully";
        }
    } elseif ($_POST['action'] == 'delete' && isset($_POST['bookIndex'])) {
        $index = $_POST['bookIndex'];
        if (isset($_SESSION['books'][$index])) {
            array_splice($_SESSION['books'], $index, 1);
            $_SESSION['update_success'] = "Book delete Successfully";
        }
    }
    else if ($_POST['action'] == 'clear') {
        $_SESSION['transactions'] = array();
        $_SESSION['transactionCounter'] = 1;
        header("Location: index.php");
        exit();
    }
}

// Handle book returning
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'return') {
    $transactionIndex = $_POST['transactionIndex'];
    if (isset($_SESSION['transactions'][$transactionIndex])) {
        $returnedBookTitle = $_SESSION['transactions'][$transactionIndex]->getBookTitle();
        foreach ($_SESSION['books'] as $book) {
            if ($book->getTitle() === $returnedBookTitle) {
                $book->increaseQuantity();
                break;
            }
        }
        array_splice($_SESSION['transactions'], $transactionIndex, 1);
        $_SESSION['update_success'] = "Book returned successfully";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Book Borrowing System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: center; }
        button { padding: 5px 10px; cursor: pointer; }
    </style>
</head>
<body>
<h2>Add New Book</h2>
    <form method="POST">
        <table>
            <tr>
                <td>Title:</td>
                <td><input type="text" name="txtTitle" required></td>
            </tr>
            <tr>
                <td>Author:</td>
                <td><input type="text" name="txtAuthor" required></td>
            </tr>
            <tr>
                <td>Quantity:</td>
                <td><input type="number" name="txtQuantity" min="0" required></td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="hidden" name="action" value="add">
                    <button type="submit">Add Book</button>
                </td>
            </tr>
        </table>
    </form>
    <h2>Available Books</h2>
    <table>
        <thead>
            <tr>
                <th>Book ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>Quantity</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['books'] as $index => $book): ?>
            <tr>
                <td><?php echo $book->getId(); ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="text" name="txtTitle" value="<?php echo htmlspecialchars($book->getTitle()); ?>">
                </td>
                <td>
                        <input type="text" name="txtAuthor" value="<?php echo htmlspecialchars($book->getAuthor()); ?>">
                </td>
                <td>
                        <input type="number" name="txtQuantity" value="<?php echo $book->getQuantity(); ?>" min="0">
                </td>
                <td>
                        <input type="hidden" name="bookIndex" value="<?php echo $index; ?>">
                        <button type="submit" name="action" value="edit">Save</button>
                        <button type="submit" name="action" value="delete" onclick="return confirm('Are you sure you want to delete this book?');">Delete</button>
                        <button type="submit" name="borrow" <?php echo $book->getQuantity() === 0 ? 'disabled' : ''; ?>>
                            Borrow
                        </button>
                        <label for="">Borrower Name:</label>
                        <input type="text" name="txtBorrower" required>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Borrowing Transactions</h2>
    <table>
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Book Title</th>
                <th>Borrower</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['transactions'] as $transactionIndex => $transaction): ?>
            <tr>
                <td><?php echo $transaction->getTransactionId(); ?></td>
                <td><?php echo $transaction->getBookTitle(); ?></td>
                <td><?php echo $transaction->getBorrower(); ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="transactionIndex" value="<?php echo $transactionIndex; ?>">
                        <button type="submit" name="action" value="return">Return</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <form method="POST" onsubmit="return confirm('Are you sure you want to clear all transactions?')">
        <input type="hidden" name="action" value="clear">
        <button type="submit">Clear All Transactions</button>
    </form>

    <?php if (isset($_SESSION['update_success'])): ?>
        <script type="text/javascript">
            alert("<?php echo $_SESSION['update_success']?>")
        </script>
        <?php unset($_SESSION['update_success']); ?>
    <?php endif;?>
</body>
</html>