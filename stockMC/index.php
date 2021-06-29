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
        <table style="border:0px;" class="stockMCTable">
            <tr>
                <td>
                    <h1>Unit Information</h1>
                </td>
            </tr>
            <tr>
                <td>
                    <table style="min-width:0;width:100%">
                        <tr>
                            <th colspan=2>Edit the Unit Standards</th>
                        </tr>
                        <tr>
                            <td style="width:55%">
                                <button type="button" onClick="redirect('editIssue.php?id=stdIssue', false)">Edit the Unit's Expected Set of Issued Items</button> <!-- USE THE INVENTORY TABLE AS THE STORAGE MEDIUM -->
                            </td>
                            <td>
                                <button type="button" onClick="redirect('editContacts.php', false)">Edit the Unit's Q Store Contacts</button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button type="button" onClick="redirect('editAppointments.php', false)">Edit the Appointment's Access Levels</button>
                            </td>
                            <td></td>
                        </tr>
                    </table>
                <td>
                    <table style="min-width:0;width:100%"> <!-- USE THE INVENTORY TABLE AS THE STORAGE MEDIUM -->
                        <tr>
                            <th colspan=2>Edit the Predefined Sets</th>
                        </tr>
                        <tr>
                            <td>
                                <button type="button" onClick="redirect('editIssue.php?id=RECIssue', false)">Standard REC Issue</button>
                            </td>
                            <td>
                                <button type="button" onClick="redirect('editIssue.php?id=AFXIssue', false)">Standard AFX Issue</button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button type="button" onClick="redirect('editIssue.php?id=customIssue', false)">Custom Issue</button>
                            </td>
                            <td></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table style="min-width:0;width:100%">
                        <tr>
                            <th><b>Upload server state: </b></th>
                        </tr>
                        <tr>
                            <td>
                                <form action="uploadSaveState.php" method="POST" enctype="multipart/form-data" onSubmit="return confirmForm()">
                                    Save state (.zip): <input type="file" id="saveState" name="saveState" required> <br>
                                    <input type="submit">
                                </form>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <button type="button" onClick="redirect('saveCurrState.php', false)">Download Current State of QSIM (For Archival / Backup Purposes)</button>
                </td>
            </tr>
            <tr> <!-- Adds a separating space -->
                <td>
                    &nbsp;
                </td>
            </tr>
            <tr>
                <td>
                    <h1>Modify Users</h1>
                </td>
                <td>
                    <h1>Modify Stock</h1>
                </td>
            </tr>
            <tr>
                <td>
                    <table style="min-width:0;width:100%">
                        <tr>
                            <th>Automated Tasks</th>
                        </tr>
                        <tr>
                            <td><button type="button" onClick="redirect('databaseProcessing.php?function=refreshAccessLevels', true)">Refresh All Access Levels</button></td>
                        </tr>
                        <tr>
                            <td><button type="button" onClick="redirect('databaseProcessing.php?function=graduateAllCadets', true)">Graduate All Cadets</button></td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table style="min-width:0;width:100%">
                        <tr>
                            <th>Automated Tasks</th>
                        </tr>
                        <tr>
                            <td><button type="button" onClick="redirect('databaseProcessing.php?function=resetLostOrDamaged', true)">Reset Lost or Damaged Numbers</button></td>
                        </tr>
                        <tr>
                            <td><button type="button" onClick="redirect('databaseProcessing.php?function=refreshStockTotals', true)">Refresh and Recalculate Stock Totals</button></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table style="min-width:0;width:100%">
                        <tr>
                            <th>Manual Entry</th>
                        </tr>
                        <tr>
                            <td><button type="button" onClick="redirect('changeUser.php?function=manualAddUser', false)">Add User</button></td>
                        </tr>
                        <tr>
                            <td><button type="button" onClick="redirect('selectUser.php?function=manualRemoveUser', false)">Remove User</button></td>
                        </tr>
                        <tr>
                            <td><button type="button" onClick="redirect('selectUser.php?function=manualModifyUser', false)">Edit User</button></td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table style="min-width:0;width:100%">
                        <tr>
                            <th colspan=2>Manual Entry</th>
                        </tr>
                        <tr>
                            <td><button type="button" onClick="redirect('modifyStock.php?action=Add', false)">Add Stock</button></td>
                            <td><button type="button" onClick="redirect('modifyStock.php?action=Remove', false)">Remove Stock</button> </td>
                        </tr>
                        <tr>
                            <td><button type="button" onClick="redirect('addItem.php', false)">Add a Stock Item</button></td>
                            <td><button type="button" onClick="redirect('removeItem.php', false)">Remove a Stock Item</button></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan=2 style="text-align:center">
                    <br><b>PLEASE ensure you understand how these function work before using them.</b> 
                    If you are unsure, you can find out more <a href="GuideToStockMC.txt" download>here</a>.
                </td>
            </tr>
            <tr>
                <td>
                    <table style="min-width:0;width:100%">
                        <tr>
                            <th>Entry via File</th>
                        </tr>
                        <tr>
                            <td><b>Add Users: </b><br>
                                <form action="fileProcessing.php?function=fileAddUsers" method="POST" enctype="multipart/form-data" onSubmit="return confirmForm()">
                                    Users' information (.csv): <input type="file" id="userInfo" name="userInfo" required> <br>
                                    Users' profile pics (.jpeg, .jpg): <input type="file" id="userPhotos" name="userPhotos[]" multiple required> <br>
                                    <input type="submit">
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Remove Users: </b><br>
                                <form action="fileProcessing.php?function=fileRemoveUsers" method="POST" enctype="multipart/form-data" onSubmit="return confirmForm()">
                                    Users' information (.csv): <input type="file" id="userInfo" name="userInfo" required> <br>
                                    <input type="submit">
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Edit Users: </b><br>
                                <form action="fileProcessing.php?function=fileUpdateUsers" method="POST" enctype="multipart/form-data" onSubmit="return confirmForm()">
                                    Users' information (.csv): <input type="file" id="userInfo" name="userInfo"> <br>
                                    Users' profile pics (.jpeg, .jpg): <input type="file" id="userPhotos" name="userPhotos[]" multiple> <br>
                                    <input type="submit">
                                </form>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table style="min-width:0;width:100%">
                        <tr>
                            <th>Entry via File</th>
                        </tr>
                        <tr>
                            <td><b>Mass Issue of Stock: </b><br>
                                <form action="fileProcessing.php?function=fileIssueStock" method="POST" enctype="multipart/form-data" onSubmit="return confirmForm()">
                                    Users' information (.csv): <input type="file" id="userInfo" name="userInfo" required> <br>
                                    <input type="submit">
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Mass Return of Stock: </b><br>
                                <form action="fileProcessing.php?function=fileReturnStock" method="POST" enctype="multipart/form-data" onSubmit="return confirmForm()">
                                    Users' information (.csv): <input type="file" id="userInfo" name="userInfo" required> <br>
                                    <input type="submit">
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Mass Declaration of Lost or Damaged: </b><br>
                                <form action="fileProcessing.php?function=fileLostStock" method="POST" enctype="multipart/form-data" onSubmit="return confirmForm()">
                                    Users' information (.csv): <input type="file" id="userInfo" name="userInfo"> <br>
                                    <input type="submit">
                                </form>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr> <!-- NEED TO MAKE THE FORMATTED EXAMPLES !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! -->
                <td style="text-align:justify">
                    If you would like a formatted example for updating or entering new users, click <a href="GuideToStockMC.txt">here</a> for the .csv and <a href="GuideToStockMC.txt">here</a> for the .xlsx which will still need to be converted into a .csv for processing.
                </td>
                <td style="text-align:justify">
                    If you would like a formatted example for mass issuing, returning or declaring lost stock, click <a href="GuideToStockMC.txt">here</a> for the .csv and <a href="GuideToStockMC.txt">here</a> for the .xlsx which will still need to be converted into a .csv for processing.
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