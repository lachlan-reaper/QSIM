<?php 
    require "../functions.php";
    session_start();
    redirectingUnauthUsers("stockMC");
?>

<html lang="en-us">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <link rel="icon" href="../images/logo.svg" sizes="any" type="image/svg+xml">
    <title>QSIM</title>
</head>

<body>
    <?php 
        displayHeader();
    ?>

    <script>
        document.getElementById("stockTab").className = "activetab";
    </script>

    <maincontents>
        <b>DO NOT REFRESH THE PAGES OR EDIT THE URLS WHILE MODIFYING ANY DATA!</b> Thank you for your cooperation :)<br> <br>
        <table>
            <tr>
                <td>
                    <h2>MODIFY STOCK</h2>
                    <b>Modify Manually:</b> <br>
                    <button type="button" onClick="redirect('modifyStock.php?action=Add', false)">Add a number of Stock</button>
                    <button type="button" onClick="redirect('modifyStock.php?action=Remove', false)">Remove a number of Stock</button> 
                    <br> <br>

                    <b>Modify via File:</b> <br>
                    <form action="fileProcessing.php?function=fileModifyStock" method="POST" onSubmit="return confirmForm()">
                        Change in Stock (.csv): <input type="file" required> <br>
                        <input type="submit">
                    </form>
                    
                    <b>Admit Stock:</b> <br>
                    <button type="button" onClick="redirect('addItem.php', false)">Add some new items of Stock</button>
                    <button type="button" onClick="redirect('removeItem.php', false)">Remove some items of Stock</button>
                </td>
                <td>
                    <h2>AUTOMATED TASKS</h2>
                    <button type="button" onClick="redirect('databaseProcessing.php?function=resetLostOrDamaged', true)">Reset Lost or Damaged</button> <br>
                    <button type="button" onClick="redirect('databaseProcessing.php?function=refreshStockTotals', true)">Refresh Stock Totals</button>
                </td>
            </tr>
            <tr>
                <td>
                    <h2>MODIFY USERS</h2>
                    <b>Manual:</b> <br>
                    <button type="button" onClick="redirect('addUser.php', false)">Add User</button>
                    <button type="button" onClick="redirect('removeUser.php', false)">Remove User</button>
                    <button type="button" onClick="redirect('modifyUser.php', false)">Modify User</button>
                    <br> <br>
                    <b>Add via File:</b> <br>
                    <form action="fileProcessing.php?function=fileAddUsers" method="POST" onSubmit="return confirmForm()">
                        Users' information (.csv): <input type="file" required> <br>
                        Users' profile pics (.jpegs): <input type="file" multiple required> <br>
                        <input type="submit">
                    </form>
                    <b>Remove via File:</b> <br>
                    <form action="fileProcessing.php?function=fileRemoveUsers" method="POST" onSubmit="return confirmForm()">
                        Users' information (.csv): <input type="file" required> <br>
                        <input type="submit">
                    </form>
                    <b>Update via File:</b> <br>
                    <form action="fileProcessing.php?function=fileUpdateUsers" method="POST" onSubmit="return confirmForm()">
                        Users' information (.csv): <input type="file" required> <br>
                        Users' profile pics (.jpegs): (OPTIONAL) <input type="file" multiple> <br>
                        <input type="submit">
                    </form>
                </td>
                <td>
                    <button type="button" onClick="redirect('databaseProcessing.php?function=refreshAccessLevels', true)">Refresh All Access Levels</button> <br>
                    <button type="button" onClick="redirect('databaseProcessing.php?function=graduateAllCadets', true)">Graduate All Cadets</button>
                </td>
            </tr>
        </table>

        <script>

            function confirmForm () {
                return confirm('Do you really want to submit the form?'); 
            }

            function redirect (URL, confirmation) {
                if (confirmation) {
                    if (confirm('Do you really want to submit the form?')) {
                        window.location.href = URL;
                    }
                } else {
                    window.location.href = URL;
                }
            }

        </script>
    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>

</html>