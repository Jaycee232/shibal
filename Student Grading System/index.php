<?php
session_start();

class Student {
    private $name;
    private $course;
    private $quizScore;
    private $examScore;
    private $projectScore;

    public function __construct($name, $course, $quizScore, $examScore, $projectScore) {
        $this->name = $name;
        $this->course = $course;
        $this->quizScore = $quizScore;
        $this->examScore = $examScore;
        $this->projectScore = $projectScore;
    }

    public function getName() {
        return $this->name;
    }

    public function getCourse() {
        return $this->course;
    }

    public function getQuizScore() {
        return $this->quizScore;
    }

    public function getExamScore() {
        return $this->examScore;
    }

    public function getProjectScore() {
        return $this->projectScore;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setCourse($course) {
        $this->course = $course;
    }

    public function setExamScore($examScore) {
        $this->examScore = $examScore;
    }

    public function setQuizScore($quizScore) {
        $this->quizScore = $quizScore;
    }

    public function setProjectScore($projectScore) {
        $this->projectScore = $projectScore;
    }
    
    public function getAverage() {
        return ($this->quizScore * 0.2 
            + $this->examScore * 0.3
            + $this->projectScore * 0.5);
    }

    public function getGradeCategory() {
        $average = $this->getAverage();

        if ($average >= 90) {
            return "Very Good";
        } else if ($average >= 80) {
            return "Good";
        } else if ($average >= 70) {
            return "Fair";
        } else if ($average >= 60) {
            return "Bad";
        } else {
            return "Very Bad";
        }
    }
}

if (!isset($_SESSION['students'])) {
    $_SESSION['students'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $name = $_POST['txtName'];
        $course = $_POST['selCourse'];
        $quizScore = (int) $_POST['txtQuizScore'];
        $examScore = (int) $_POST['txtExamScore'];
        $projectScore = (int) $_POST['txtProjectScore'];

        if (!empty($name) && !empty($course) && !empty($quizScore) && !empty($examScore) && !empty($projectScore)) {
            $newStudent = new Student($name, $course, $quizScore, $examScore, $projectScore);
            $_SESSION['students'][] = $newStudent;
            $_SESSION['update_success'] = "Student added successfully";
        }
        header("Location: index.php");
        exit();
    }
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'edit' && isset($_GET['index']) && isset($_GET['txtName']) && isset($_GET['selCourse']) 
        && isset($_GET['txtQuizScore']) && isset($_GET['txtExamScore']) && isset($_GET['txtProjectScore'])) {
        
        $index = $_GET['index'];
        $name = $_GET['txtName'];
        $course = $_GET['selCourse'];
        $quizScore = (int) $_GET['txtQuizScore'];
        $examScore = (int) $_GET['txtExamScore'];
        $projectScore = (int) $_GET['txtProjectScore'];

        if (isset($_SESSION['students'][$index])) {
            $_SESSION['students'][$index]->setName($name);
            $_SESSION['students'][$index]->setCourse($course);
            $_SESSION['students'][$index]->setQuizScore($quizScore);
            $_SESSION['students'][$index]->setExamScore($examScore);
            $_SESSION['students'][$index]->setProjectScore($projectScore);
            $_SESSION['update_success'] = "Student updated successfully";
        }

        header("Location: index.php");
        exit();
    }

    if ($action == 'delete' && isset($_GET['index'])) {
        $index = $_GET['index'];

        if (isset($_SESSION['students'][$index])) {
            array_splice($_SESSION['students'], $index, 1);
        }
        header("Location: index.php");
        exit();
    }

    if ($action == 'clear') {
        unset($_SESSION['students']);
        header("Location: index.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grading System</title>
    <link rel="stylesheet" href="style.css">
    
</head>
<body>
    <h1>Student Grading System</h1>

    <form action="index.php" method="post">
        <input type="hidden" name="action" value="add">
        <div class="form-row">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="txtName" required>
            </div>
            <div class="form-group">
                <label>Course/Program</label>
                <select name="selCourse">
                    <option value="BSIT">BSIT</option>
                    <option value="BSIS">BSIS</option>
                    <option value="BSCS">BSCS</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Quiz Score:</label>
                <input type="number" name="txtQuizScore" required min="1" max="100">
            </div>
            <div class="form-group">
                <label>Exam Score:</label>
                <input type="number" name="txtExamScore" required min="1" max="100">
            </div>
            <div class="form-group">
                <label>Project Score:</label>
                <input type="number" name="txtProjectScore" required min="1" max="100">
            </div>
        </div>
        <input type="submit" value="Submit">
    </form>

    <h2>Students List</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Course/Program</th>
                <th>Quiz Score</th>
                <th>Exam Score</th>
                <th>Project Score</th>
                <th>Average</th>
                <th>Grade Category</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['students'] as $index => $student): ?>
            <tr>
                <form action="index.php" method="get">
                    <td>
                        <input type="text" name="txtName" value="<?php echo htmlspecialchars($student->getName()); ?>" required>
                    </td>
                    <td>
                        <select name="selCourse">
                            <option value="" disabled>Select Course/Program</option>
                            <option value="BSIT" <?php if ($student->getCourse() == 'BSIT') echo "selected"; ?>>BSIT</option>
                            <option value="BSIS" <?php if ($student->getCourse() == 'BSIS') echo "selected"; ?>>BSIS</option>
                            <option value="BSCS" <?php if ($student->getCourse() == 'BSCS') echo "selected"; ?>>BSCS</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="txtQuizScore" value="<?php echo htmlspecialchars($student->getQuizScore()); ?>" required>
                    </td>
                    <td>
                        <input type="number" name="txtExamScore" value="<?php echo htmlspecialchars($student->getExamScore()); ?>" required>
                    </td>
                    <td>
                        <input type="number" name="txtProjectScore" value="<?php echo htmlspecialchars($student->getProjectScore()); ?>" required>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($student->getAverage()); ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($student->getGradeCategory()); ?>
                    </td>
                    <td>
                        <input type="hidden" name="index" value="<?php echo $index; ?>">
                        <button type="submit" name="action" value="edit">Save</button>
                        <button type="submit" name="action" value="delete" onclick="return confirm('Are you sure you want to delete this student?')">Delete</button>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <form action="index.php" method="get" onsubmit="return confirm('Are you sure you want to clear all students?')">
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
