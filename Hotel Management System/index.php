<?php
session_start();

class Room {
    private $name;
    private $age;
    private $email;
    private $roomType;
    private $numberOfNights;

    public function __construct($name, $age, $email, $roomType, $numberOfNights){
        $this->name = $name;
        $this->age = $age;
        $this->email = $email;
        $this->roomType = $roomType;
        $this->numberOfNights = $numberOfNights;
    }

    public function getName(){return $this->name;}
    public function getAge(){return $this->age;}
    public function getEmail(){return $this->email;}
    public function getRoomType(){return $this->roomType;}
    public function getNumberOfNights(){return $this->numberOfNights;}

    public function setName($name){$this->name = $name;}
    public function setAge($age){$this->age = $age;}
    public function setEmail($email){$this->email = $email;}
    public function setRoomType($roomType){$this->roomType = $roomType;}
    public function setNumberOfNights($numberOfNights){$this->numberOfNights = $numberOfNights;}

    public function pricingRoom(){
        switch($this->roomType){
            case 'Standard Room':  
                return 5000;
            case 'Deluxe Room':
                return 10000;
            case 'Conference Room':
                return 15000;
            default:
                return 0;  
        }
    }
    
    public function totalAmount() {
        $baseAmount = $this->pricingRoom() * $this->numberOfNights;
        
        switch ($this->roomType){
            case 'Standard Room':
                return $baseAmount + ($baseAmount * 0.12); // 12% tax
            case 'Deluxe Room':
                return $baseAmount + ($baseAmount * 0.12); // 12% tax
            case 'Conference Room':
                return $baseAmount + ($baseAmount * 0.08); // 8% service charge
            default:
                return 0;
        }
    }
}
if (!isset($_SESSION['customers'])){
    $_SESSION['customers'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $name = $_POST['txtName'];
        $age = $_POST['txtAge'];
        $email = $_POST['txtEmail'];
        $roomType = $_POST['selRoomType'];
        $numberOfNights = (int) $_POST['txtNumberOfNights'];

        if (!empty($name) && !empty($age) && !empty($email) && !empty($roomType) && !empty($numberOfNights)) {
            $newCustomer = new Room($name, $age, $email, $roomType, $numberOfNights);
            $_SESSION['customers'][] = $newCustomer;
            $_SESSION['update_success'] = "Customer added successfully!";
        }
        header("Location: index.php");
        exit();
    }
}
if (isset ($_GET['action'])){
    $action = $_GET['action'];

    if ($action == 'edit' && isset($_GET['index']) && isset($_GET['txtName']) && isset($_GET['txtAge']) && isset($_GET['txtEmail']) && isset($_GET['selRoomType']) && isset($_GET['txtNumberOfNights'])){
        $index = $_GET['index'];
        $name = $_GET['txtName'];
        $age = $_GET['txtAge'];
        $email = $_GET['txtEmail'];
        $roomType = $_GET['selRoomType'];
        $numberOfNights = (int) $_GET['txtNumberOfNights'];

        if (isset($_SESSION['customers'] [$index])){
            $_SESSION['customers'][$index] ->setName($name);
            $_SESSION['customers'][$index] ->setAge($age);
            $_SESSION['customers'][$index] ->setEmail($email);
            $_SESSION['customers'][$index] ->setRoomType($roomType);
            $_SESSION['customers'][$index] ->setNumberOfNights($numberOfNights);
            $_SESSION['update_success'] = "Customer updated successfully!";
        }
        header("Location: index.php");
        exit();
    }

    if ($action == 'delete' && isset($_GET['index'])){
        $index = $_GET['index'];

        if (isset($_SESSION['customers'][$index])){
            array_splice($_SESSION['customers'], $index, 1);
        }
        header("Location: index.php");
        exit();
    }

    if ($action == 'clear'){
        unset($_SESSION['customers']);
        header("Location: index.php");
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Hotel Management System</h1>

    <form action="index.php" method="post">
        <input type="hidden" name="action" value="add">

        <label>Name:</label>
        <input type="text" name="txtName" required>
        <label>Age:</label>
        <input type="number" name="txtAge" required>
        <label>Email:</label>
        <input type="email" name="txtEmail" required>
        <label>Room Type:</label>
        <select name="selRoomType">
            <option value="" disabled>Select Room Type</option>
            <option value="Standard Room">Standard Room</option>
            <option value="Deluxe Room">Deluxe Room</option>
            <option value="Conference Room">Conference Room</option>
        </select>
        <label>Number of Nights:</label>
        <input type="number" name="txtNumberOfNights"  required min="1">
        <input type="submit" value="Add Customer">
    </form>

    <h2>Customer List</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Age</th>
                <th>Email</th>
                <th>Room Type</th>
                <th>Number of Nights</th>
                <th>Total Amount:</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['customers'] as $index => $customer): ?>
                <tr>
                    <form action="index.php" method="get">
                        <td>
                            <input type="text" name="txtName" value="<?php echo htmlspecialchars($customer->getName()); ?>" required>
                        </td>
                        <td>
                            <input type="number" name="txtAge" value="<?php echo htmlspecialchars($customer->getAge()); ?>" required>
                        </td>
                        <td>
                            <input type="email" name="txtEmail" value="<?php echo htmlspecialchars($customer->getEmail()); ?>" required>
                        </td>

                        <td>
                            <select name="selRoomType">
                                <option value="" disabled>Select Room Type</option>
                                <option value="Standard Room" <?php if ($customer->getRoomType() == 'Standard Room') echo "selected"; ?>>Standard Room</option>
                                <option value="Deluxe Room" <?php if ($customer->getRoomType() == 'Deluxe Room') echo "selected"; ?>>Deluxe Room</option>
                                <option value="Conference Room" <?php if ($customer->getRoomType() == 'Conference Room') echo "selected"; ?>>Conference Room</option>
                            </select>
                        </td>

                        <td>
                            <input type="number" name="txtNumberOfNights" value="<?php echo htmlspecialchars($customer->getNumberOfNights()); ?>" required> 
                        </td>

                        <td>
                            Php <?php echo htmlspecialchars($customer->totalAmount()); ?>
                        </td>

                        <td>
                            <input type="hidden" name="index" value="<?php echo $index?>">
                            <button type="submit" name="action" value="edit">Save</button>
                            <button type="submit" name="action" value="delete" onclick="return confirm('Are you sure you want to delete this customer?')">Delete</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <form action="index.php" method="get" onsubmit="return confirm('Are you sure you want to clear all customers?')">
        <input type="hidden" name="action" value="clear">
        <button type="submit">Clear All</button>
    </form>

    <?php if (isset($_SESSION['update_success'])): ?>
        <script type="text/javascript">
            alert("<?php echo $_SESSION['update_success']; ?>");
        </script>
        <?php unset($_SESSION['update_success']); ?>
    <?php endif; ?>
</body>
</html>