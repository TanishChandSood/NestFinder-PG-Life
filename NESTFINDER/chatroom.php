<?php
session_start();
include "includes/database_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$current_user_id = intval($_SESSION['user_id']);
$property_id = isset($_GET['property_id']) ? intval($_GET['property_id']) : 1;


if (isset($_GET['receiver_id'])) {
    $receiver_id = intval($_GET['receiver_id']);
} elseif (isset($_GET['roommate_id'])) {
    $receiver_id = intval($_GET['roommate_id']);
} else {
    $receiver_id = 0;
}

if ($receiver_id == 0) {
    echo "<div class='container mt-5 alert alert-danger'>Error: No chat recipient selected!</div>";
    exit;
}


$query_receiver = "SELECT full_name FROM users WHERE id = ?";
$stmt_rec = mysqli_prepare($conn, $query_receiver);
mysqli_stmt_bind_param($stmt_rec, "i", $receiver_id);
mysqli_stmt_execute($stmt_rec);
$res_receiver = mysqli_stmt_get_result($stmt_rec);
$receiver_data = mysqli_fetch_assoc($res_receiver);
$receiver_name = $receiver_data ? htmlspecialchars($receiver_data['full_name'], ENT_QUOTES, 'UTF-8') : "User";
mysqli_stmt_close($stmt_rec);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Chat with <?= $receiver_name ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .chat-box {
            height: 420px;
            overflow-y: auto;
            background: #fdfdfd;
            border: 1px solid #eef2f3;
            border-radius: 12px;
            padding: 20px;
        }

        .msg {
            max-width: 65%;
            padding: 10px 14px;
            border-radius: 18px;
            margin-bottom: 12px;
            font-size: 15px;
            position: relative;
        }

        .msg.me {
            background: #007bff;
            color: #fff;
            margin-left: auto;
            border-bottom-right-radius: 2px;
            text-align: left;
        }

        .msg.other {
            background: #f1f3f5;
            color: #333;
            margin-right: auto;
            border-bottom-left-radius: 2px;
        }

        .sender-name {
            font-size: 11px;
            font-weight: bold;
            color: #6c757d;
            margin-bottom: 2px;
            display: block;
        }

        .msg.me .sender-name {
            color: #e1f0ff;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow border-0" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 font-weight-bold">💬 Chat with <?= $receiver_name ?></h5>
                        <a href="<?php echo ($_SESSION['role'] == 'owner') ? 'owner_dashboard.php' : 'dashboard.php'; ?>" class="btn btn-sm btn-outline-light" style="border-radius: 20px;">Back to Dashboard</a>
                    </div>
                    <div class="card-body bg-white p-4">
                        <div class="chat-box mb-3" id="chatBox"></div>

                        <form id="chatForm" class="d-flex">
                            <input type="hidden" id="propertyId" value="<?= $property_id ?>">
                            <input type="hidden" id="receiverId" value="<?= $receiver_id ?>">

                            <input type="text" id="messageInput" class="form-control mr-2" placeholder="Type a message..." style="border-radius: 25px; padding: 22px 15px;" required autocomplete="off">
                            <button type="submit" class="btn btn-primary px-4" style="border-radius: 25px; font-weight: bold;">Send</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const propertyId = $('#propertyId').val();
            const receiverId = $('#receiverId').val();
            const chatBox = $('#chatBox');


            console.log("Property ID Loaded:", propertyId);
            console.log("Receiver ID Loaded:", receiverId);

            function loadMessages() {
                $.ajax({
                    url: 'fetch_messages.php',
                    type: 'GET',
                    data: {
                        property_id: propertyId,
                        receiver_id: receiverId
                    },
                    dataType: 'json',
                    success: function(data) {
                        let chatHTML = '';
                        if (data && data.length > 0) {
                            data.forEach(function(msg) {
                                let msgClass = msg.is_me ? 'me' : 'other';
                                chatHTML += `<div class="msg ${msgClass}">
                                        <span class="sender-name">${msg.full_name}</span>
                                        <div>${msg.message}</div>
                                     </div>`;
                            });
                            chatBox.html(chatHTML);
                            chatBox.scrollTop(chatBox[0].scrollHeight);
                        } else {
                            chatBox.html('<div class="text-center text-muted">No messages yet.</div>');
                        }
                    }
                });
            }

            setInterval(loadMessages, 2000);
            loadMessages();

            $('#chatForm').on('submit', function(e) {
                e.preventDefault();
                const message = $('#messageInput').val();

                $.ajax({
                    url: 'send_message.php',
                    type: 'POST',
                    data: {
                        property_id: propertyId,
                        receiver_id: receiverId,
                        message: message
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#messageInput').val('');
                        loadMessages();
                    },
                    error: function(xhr, status, error) {
                        console.error("Chat send error: ", error);
                    }
                });
            });
        });
    </script>
</body>

</html>