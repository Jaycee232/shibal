<?php
session_start();

class Employee {
    private $name;
    private $position;
    private $dateOfEmployment;

    public function __construct($name, $position, $dateOfEmployment) {
        $this->name = $name;
        $this->position = $position;
        $this->dateOfEmployment = $dateOfEmployment;
    }

    public function getName() { return $this->name; }
    public function getPosition() { return $this->position; }
    public function getDateOfEmployment() { return $this->dateOfEmployment; }
    
    public function setName($name) { $this->name = $name; }
    public function setPosition($position) { $this->position = $position; }
    public function setDateOfEmployment($dateOfEmployment) { $this->dateOfEmployment = $dateOfEmployment; }

    public function getMonthlySalary() {
        switch($this->position) {
            case "Manager":
                return 7000;
            case "Developer":
                return 5000;
            case "Designer":
                return 4000;
            case "Intern":
                return 2000;
            default:
                return 0;
        }
    }

    public function getAnnualBonus() {
        switch ($this->position) {
            case 'Manager':
                return $this->getMonthlySalary() * 0.20;
            case 'Developer':
                return $this->getMonthlySalary() * 0.10;
            case 'Designer':
                return $this->getMonthlySalary() * 0.05;
            case 'Intern':
                return $this->getMonthlySalary() * 0.00;
            default:
                return 0;
        }
    }
}

if (!isset($_SESSION['employees'])) {
    $_SESSION['employees'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $name = $_POST['txtName'];
        $position = $_POST['selPosition'];
        $dateOfEmployment = $_POST['txtDate'];

        if (!empty($name) && !empty($position) && !empty($dateOfEmployment)) {
            $newEmployee = new Employee($name, $position, $dateOfEmployment);
            $_SESSION['employees'][] = $newEmployee;
            $_SESSION['update_success'] = "Employee added successfully!";
        }

        header("Location: index.php");
        exit();
    }
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'edit' && isset($_GET['index']) && isset($_GET['txtName']) && isset($_GET['selPosition'])) {
        $index = $_GET['index'];
        $name = $_GET['txtName'];
        $position = $_GET['selPosition'];
        $dateOfEmployment = $_GET['txtDate'];

        if (isset($_SESSION['employees'][$index])) {
            $_SESSION['employees'][$index]->setName($name);
            $_SESSION['employees'][$index]->setPosition($position);
            $_SESSION['employees'][$index]->setDateOfEmployment($dateOfEmployment);
            $_SESSION['update_success'] = "Employee updated successfully!";
        }
        header("Location: index.php");
        exit();
    }

    if ($action == 'delete' && isset($_GET['index'])) {
        $index = $_GET['index'];
        if (isset($_SESSION['employees'][$index])) {
            array_splice($_SESSION['employees'], $index, 1);
        }
        header("Location: index.php");
        exit();
    }

    if ($action == 'clear'){
        unset($_SESSION['employees']);

        header("Location: index.php");
        
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINALS LABORATORY EXERCISE</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Employee Management System</h1>
    
    <form action="index.php" method="post">
        <input type="hidden" name="action" value="add">
        
        <label>Name:</label>
        <input type="text" name="txtName" required>
        
        <label>Position</label>
        <select name="selPosition">
            <option value="Manager">Manager</option>
            <option value="Developer">Developer</option>
            <option value="Designer">Designer</option>
            <option value="Intern">Intern</option>
        </select>
        
        <label>Date of Employment</label>
        <input type="date" name="txtDate" required>
        
        <input type="submit" value="Add Employee">
    </form>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Date of Employment</th>
                <th>Monthly Salary</th>
                <th>Annual Bonus Rate</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['employees'] as $index => $employee): ?>
                <tr>
                    <form method="get" action="index.php">
                        <td>
                            <input type="text" name="txtName" value="<?php echo htmlspecialchars($employee->getName()); ?>" required>
                        </td>
                        <td>
                            <select name="selPosition">
                                <option value="Manager" <?php if ($employee->getPosition() == "Manager") echo "selected"; ?>>Manager</option>
                                <option value="Developer" <?php if ($employee->getPosition() == "Developer") echo "selected"; ?>>Developer</option>
                                <option value="Designer" <?php if ($employee->getPosition() == "Designer") echo "selected"; ?>>Designer</option>
                                <option value="Intern" <?php if ($employee->getPosition() == "Intern") echo "selected"; ?>>Intern</option>
                            </select>                         
                        </td>
                        <td>
                            <input type="date" name="txtDate" value="<?php echo htmlspecialchars($employee->getDateOfEmployment()); ?>" required>
                        </td>
                        <td>
                            Php <?php echo htmlspecialchars($employee->getMonthlySalary()); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($employee->getAnnualBonus()); ?> 
                        </td>
                        <td>
                            <input type="hidden" name="index" value="<?php echo $index?>">
                            <button type="submit" name="action" value="edit">Save</button>
                            <button type="submit" name="action" value="delete" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <form method="get" action="index.php" onsubmit="return confirm('Are you sure you want to clear all Employee?');">
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