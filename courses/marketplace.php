<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch tracks not yet acquired by the student
$stmt = $pdo->prepare("
    SELECT c.*, u.username as instructor 
    FROM courses c 
    JOIN users u ON c.instructor_id = u.user_id 
    WHERE c.course_id NOT IN (SELECT course_id FROM enrollments WHERE user_id = ?)
");
$stmt->execute([$_SESSION['user_id']]);
$available = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bazaar Meta | All Items</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            background: #0b0b0b;
            display: block;
            padding-bottom: 50px;
        }

        /* Marketplace Header [ref: image_4a7e1a.png] */
        .market-header {
            text-align: center;
            padding: 60px 20px;
        }

        .market-header h1 {
            font-size: 3em;
            letter-spacing: -2px;
            margin-bottom: 10px;
        }

        .market-header p {
            color: #666;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .market-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            padding: 0 20px;
        }

        /* Item Card [ref: image_4a7e1a.png] */
        .item-card {
            background: #161616;
            border: 1px solid #222;
            border-radius: 12px;
            padding: 30px 20px;
            text-align: center;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .item-card:hover {
            border-color: #00acee;
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(0, 172, 238, 0.1);
        }

        .item-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            background: #1a1a1a;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2em;
            border: 1px solid #333;
        }

        .price-tag {
            color: #aaa;
            font-size: 0.85em;
            margin: 15px 0;
            font-weight: bold;
        }

        .price-tag span {
            color: #fff;
        }

        .btn-acquire {
            display: block;
            width: 100%;
            background: #00acee;
            color: #000;
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 900;
            font-size: 0.8em;
            letter-spacing: 1px;
            transition: 0.2s;
        }

        .btn-acquire:hover {
            background: #00d4ff;
        }
    </style>
</head>

<body>

    <header class="market-header">
        <h1>All Items</h1>
        <p>This page shows the maximum knowledge per hour that can be made flipping each track. Choose your contract
            wisely.</p>
    </header>

    <div class="market-grid">
        <?php foreach ($available as $item): ?>
            <div class="item-card">
                <div class="item-icon">📜</div>
                <h3 style="margin:0; font-size:1.1em;"><?php echo htmlspecialchars($item['title']); ?></h3>
                <p style="font-size:0.75em; color:#555; margin-top:5px;">Instructor:
                    <?php echo htmlspecialchars($item['instructor']); ?></p>

                <div class="price-tag">
                    Buy Price: <span><?php echo number_format($item['price'], 2); ?> coins</span>
                </div>

                <a href="enroll.php?id=<?php echo $item['course_id']; ?>" class="btn-acquire">ACQUIRE CONTRACT</a>
            </div>
        <?php endforeach; ?>

        <?php if (empty($available)): ?>
            <div style="grid-column: 1/-1; text-align: center; color: #444; padding: 100px;">
                The Bazaar is empty. All current contracts have been signed.
            </div>
        <?php endif; ?>
    </div>

</body>

</html>