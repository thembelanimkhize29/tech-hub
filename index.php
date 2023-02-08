<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Webflow System</title>
    <!--link to css file -->
    <link rel="stylesheet" href="mystyle.css">

    <!--loading the Google Chart libraries -->
    <!--load the loader itself -->
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script><!--load the loader itself -->
    <script type="text/javascript">
        // Load the Visualization API and the barchart package
        google.charts.load('current', {
            'packages': ['bar']
        });
        google.charts.setOnLoadCallback(drawChart); //will be called once the packages have been loaded

        // Callback that creates and populates a data table, 
        function drawChart() {
            var data = google.visualization.arrayToDataTable([ //data to be wrapped in a JavaScript class
                ['month', 'income', 'expenses'],
                <?php
                $res = mysqli_query($conn, "select * from tb_data");
                while ($data = mysqli_fetch_array($res)) {
                    $month = $data['month'];
                    $income = $data['income'];
                    $expenses = $data['expenses'];
                ?>['<?php echo $month; ?>', <?php echo $income; ?>, <?php echo $expenses; ?>],
                <?php
                }
                ?>
            ]);
            // Set chart options
            var options = {
                chart: {
                    title: 'Financial Income and Expenses',
                },
                bars: 'vertical'
            };

            // Instantiate and draw our chart, passing in some options.
            var chart = new google.charts.Bar(document.getElementById('barchart_material'));

            chart.draw(data, google.charts.Bar.convertOptions(options));
        }
    </script>

</head>

<body>
    <form class="" action="" enctype="multipart/form-data" method="post">
        <!--user input -->
        <label for="fname">First name:</label><br />
        <input type="text" name="firstname" id="firstname_id" placeholder="firstnames" required />
        <br />
        <label for="fname">Last name:</label><br />
        <input type="text" name="surname" id="surname_id" placeholder="surname" required />
        <br />
        <label for="birthday">Birthday:</label><br />
        <input type="date" id="birthday" name="birthday" required>
        <br />
        <br />

        <input type="file" name="excel" required value="">
        <!-- Button submit insert data to the database -->
        <button type="submit" name="import">Submit</button>
        <br />
    </form>


    <?php
    // when button submit is clicked and there is no missing information from the form
    if (isset($_POST["import"])) {
        $fname = $_POST["firstname"];
        $lname = $_POST["surname"];
        $dob = $_POST["birthday"];

        $fileName = $_FILES["excel"]["name"];
        $fileExtension = explode('.', $fileName);
        $fileExtension = strtolower(end($fileExtension));

        $newFileName = date("Y.m.d") . " - " . date("h.i.sa") . "." . $fileExtension;

        $targetDirectory = "uploads/" . $newFileName;
        move_uploaded_file($_FILES["excel"]["tmp_name"], $targetDirectory);

        error_reporting(0);
        ini_set('display_errors', 0);

        require "excelReader/excel_reader2.php";
        require "excelReader/SpreadsheetReader.php";

        $reader = new SpreadsheetReader($targetDirectory);
        foreach ($reader as $key => $row) {
            $month = $row[0];
            $income = $row[1];
            $expenses = $row[2];

            //insert data to the database name data
            mysqli_query($conn, "INSERT INTO tb_data VALUES('','$month','$income','$expenses')");
            mysqli_query($conn, "INSERT INTO customer VALUES('','$fname','$lname','$dob')");
        }
        //feedback if data is successfully sent to the database
        echo
        "
                <script>
                alert('Successfully Imported');
                document.location.href = '';
                </script>
        ";
    }
    ?>
    <br />
    <!--Div that will hold the bar chart-->
    <div id="barchart_material" style="width: 900px; height: 500px;"></div>
</body>

</html>