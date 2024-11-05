<?php
require "koneksi.php";
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['loggedin'])) {
    header('Location: phplogin/index.html');
    exit();
}

// Dapatkan ID pengguna dari sesi
$user_id = $_SESSION['id'];

// Ambil data keranjang dari database
$queryCart = mysqli_query($con, "SELECT cart.*, produk.nama, produk.harga, produk.foto FROM cart JOIN produk ON cart.product_id = produk.id WHERE cart.user_id = '$user_id'");


$order_id = "ORD" . date("YmdHis");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link rel="stylesheet" href="cart.css">
    <link rel="stylesheet" href="styles.css">
    <style>
       /* CSS for Modal */
    .modal {
        display: none; 
        position: fixed; 
        z-index: 1; 
        left: 0;
        top: 0;
        width: 100%; 
        height: 100%; 
        overflow: auto; 
        background-color: rgba(0,0,0,0.7); 
        padding-top: 60px;
        transition: opacity 0.3s ease; 
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto; 
        padding: 20px;
        border-radius: 5px; /* Rounded corners */
        box-shadow: 0 4px 8px rgba(0,0,0,0.2); 
        width: 50%; 
        max-height: 80%; 
        overflow-y: auto;
        animation: slideIn 0.5s ease-out; /* Slide-in animation */
    }

    @keyframes slideIn {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        transition: color 0.3s ease; /* Smooth color transition */
    }

    .close:hover,
    .close:focus {
        color: #333; /* Darker color on hover */
        text-decoration: none;
        cursor: pointer;
    }

    /* Add custom button styles */
    .checkout-button a {
        display: inline-block;
        padding: 10px 20px;
        background-color: #50413d;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .checkout-button a:hover {
        background-color: #555;
    }

    </style>
</head>
<body>
    <nav>
        <div class="logo">
            <img src="images/navLogo.svg">
        </div>

        <ul>
            <li><a href="homepage.php">Home</a></li>
            <li><a href="collections.php">Collection</a></li>
            <li><a href="about_us.php">About Us</a></li>
            <?php
            if (isset($_SESSION['loggedin'])) {
                // apabila user telah login, makan tombol account akan berubah ke Profile
                echo '<div class="accCart">
                            <li><img src="images/account_icon.png" style="height: 25px;"><a href="phplogin/profile.php">Profile</a></li>
                            <li><img src="images/cart_icon.png" style="height: 25px;"><a href="cart.php">Cart</a></li>
                        </div>';
            } else {
                // jika belum login, tampilkan login link
                echo '<div class="accCart">
                            <li><img src="images/account_icon.png" style="height: 25px;"><a href="phplogin/index.html">Login</a></li>
                            <li><img src="images/cart_icon.png" style="height: 25px;"><a href="cart.php">Cart</a></li>
                        </div>';
            }
            ?>
        </ul>

        <div class="menu-toggle">
            <input type="checkbox" name="" id="" />
            <span></span>
            <span></span>
            <span></span>
    </nav>

    <section class="cart">
        <h1>Keranjang Belanja</h1>
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Ukuran</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                while ($cartItem = mysqli_fetch_array($queryCart)) {
                    $subtotal = $cartItem['harga'] * $cartItem['quantity'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td>
                        <div class="produkNama">
                            <img src="images/<?php echo $cartItem['foto']; ?>" alt="<?php echo $cartItem['nama']; ?>" width="60">
                            <p><?php echo $cartItem['nama']; ?></p>
                        </div>
                    </td>
                    <td><?php echo $cartItem['size']; ?></td>
                    <td><?php echo $cartItem['quantity']; ?></td>
                    <td>Rp.<?php echo $cartItem['harga']; ?></td>
                    <td>Rp.<?php echo $subtotal; ?></td>
                    <td>
                        <form action="remove_from_cart.php" method="post">
                            <input type="hidden" name="cart_id" value="<?php echo $cartItem['id']; ?>">
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td colspan="4">Total</td>
                    <td>Rp.<?php echo $total; ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="checkout-button">
            <a href="javascript:void(0)" onclick="showModal(<?php echo $total; ?>)">Lanjutkan ke Pembayaran</a>
        </div>
    </section>

    <!-- The Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <iframe id="paymentFrame" src="" width="100%" height="600px"></iframe>
        </div>
    </div>

    <script>
        function showModal(total) {
            document.getElementById('paymentFrame').src = 'midtrans/examples/snap/index.php?order_id=<?php echo $order_id; ?>&total=' + total;
            document.getElementById('paymentModal').style.display = "block";
        }

        // Get the modal
        var modal = document.getElementById('paymentModal');

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
    </section>

    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="images/batikliciousLogoFooter.svg" alt="">
            </div>
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Batik Licious adalah sebuah situs website yang menyediakan tempat untuk pembelian batik secara
                    online. Dengan memberikan deskripsi batik secara rinci mulai dari corak, motif, pola hingga
                    bahan yang digunakan dengan harga terjangkau dan kualitas terbaik. </p>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: contact@example.com</p>
                <p>Phone: +1234567890</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path fill="currentColor" fill-rule="evenodd" d="M3 8a5 5 0 0 1 5-5h8a5 5 0 0 1 5 5v8a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5V8Zm5-3a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H8Zm7.597 2.214a1 1 0 0 1 1-1h.01a1 1 0 1 1 0 2h-.01a1 1 0 0 1-1-1ZM12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm-5 3a5 5 0 1 1 10 0 5 5 0 0 1-10 0Z" clip-rule="evenodd"/>
                      </svg>
                    <a href="#" class="social-link">Instagram</a>
                </div>
                <div class="social-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M13.135 6H15V3h-1.865a4.147 4.147 0 0 0-4.142 4.142V9H7v3h2v9.938h3V12h2.021l.592-3H12V6.591A.6.6 0 0 1 12.592 6h.543Z" clip-rule="evenodd"/>
                      </svg>  
                    <a href="#" class="social-link">Facebook</a>
                </div>
                <div class="social-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M22 5.892a8.178 8.178 0 0 1-2.355.635 4.074 4.074 0 0 0 1.8-2.235 8.343 8.343 0 0 1-2.605.981A4.13 4.13 0 0 0 15.85 4a4.068 4.068 0 0 0-4.1 4.038c0 .31.035.618.105.919A11.705 11.705 0 0 1 3.4 4.734a4.006 4.006 0 0 0 1.268 5.392 4.165 4.165 0 0 1-1.859-.5v.05A4.057 4.057 0 0 0 6.1 13.635a4.192 4.192 0 0 1-1.856.07 4.108 4.108 0 0 0 3.831 2.807A8.36 8.36 0 0 1 2 18.184 11.732 11.732 0 0 0 8.291 20 11.502 11.502 0 0 0 19.964 8.5c0-.177 0-.349-.012-.523A8.143 8.143 0 0 0 22 5.892Z" clip-rule="evenodd"/>
                    </svg>
                    <a href="#" class="social-link">Twitter</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
