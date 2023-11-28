<?php
include 'dbc.php';
// Initialize the shopping list as an empty array
$shoppingList = [];
// echo "Index to delete";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add item to the shopping list
    if (isset($_POST['addItem'])) {
        $newItem = trim($_POST['newItem']);
        if (!empty($newItem)) {
            // Insert new item into the database
            $stmt = $conn->prepare("INSERT INTO shopping_list (item, done) VALUES (?, 0)");
            $stmt->bind_param("s", $newItem);
            $stmt->execute();
            $stmt->close();

            // Redirect to prevent form resubmission
            header("Location: {$_SERVER['PHP_SELF']}");
            exit();
        }
    }

    // Delete item from the shopping list
    if (isset($_POST['deleteItem'])) {
        $index = $_POST['deleteItem'];
        // Delete item from the database
        $stmt = $conn->prepare("DELETE FROM shopping_list WHERE id = ?");
        $stmt->bind_param("i", $index);
        $stmt->execute();
        $stmt->close();

        // Redirect to prevent form resubmission
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }

    // Update item status in the shopping list (mark as done)
    if (isset($_POST['toggleDone'])) {
        $index = $_POST['toggleDone'];
        // Toggle item status in the database
        $stmt = $conn->prepare("UPDATE shopping_list SET done = NOT done WHERE id = ?");
        $stmt->bind_param("i", $index);
        $stmt->execute();
        $stmt->close();

        // Redirect to prevent form resubmission
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }

    // Edit item in the shopping list
    if (isset($_POST['saveItem'])) {
        $index = $_POST['saveItem'];
        $editedItem = trim($_POST['editedItem']);

        if (!empty($editedItem)) {
            // Update item in the database
            $stmt = $conn->prepare("UPDATE shopping_list SET item = ? WHERE id = ?");
            $stmt->bind_param("si", $editedItem, $index);
            $stmt->execute();
            $stmt->close();

            // Redirect to prevent form resubmission
            header("Location: {$_SERVER['PHP_SELF']}");
            exit();
        }
    }
}

// Retrieve shopping list from the database
$result = $conn->query("SELECT id, item, done FROM shopping_list");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $shoppingList[] = ['id' => $row['id'], 'item' => $row['item'], 'done' => (bool)$row['done']];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping List</title>
</head>

<body class="body_wrapper">
<main>
<h1>Shopping List</h1>
    <form method="post" action="">
        <label for="newItem">Add Item:</label>
        <input type="text" id="newItem" name="newItem" required>
        <button type="submit" name="addItem">Add</button>
    </form>
    <ul>
        <?php foreach ($shoppingList as $item): ?>
        <li class="<?php echo $item['done'] ? 'done' : ''; ?>">
            <form method="post" action="">
                <input type="checkbox" name="toggleDone" value="<?php echo $item['id']; ?>" onchange="this.form.submit()"
                    <?php echo $item['done'] ? 'checked' : ''; ?>>

                <?php if (!isset($_POST['editItem']) || $_POST['editItem'] != $item['id']): ?>
                    <span><?php echo htmlspecialchars($item['item']); ?></span>
                    <button type="submit" name="editItem" value="<?php echo $item['id']; ?>">Edit</button>
                <?php else: ?>
                    <input type="text" name="editedItem" value="<?php echo htmlspecialchars($item['item']); ?>" required>
                    <button type="submit" name="saveItem" value="<?php echo $item['id']; ?>">Save</button>
                <?php endif; ?>

                <button type="submit" name="deleteItem" value="<?php echo $item['id']; ?>">Delete</button>
            </form>
        </li>
        <?php endforeach; ?>
    </ul>
</main>

<script>
        function toggleDone(id) {
            // Simulate form submission for toggling item status
            var form = document.createElement('form');
            form.method = 'post';
            form.action = '';
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'toggleDone';
            input.value = id;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>