

<?php
	include "config.php";

	if(!empty($_POST['suhu']) && !empty($_POST['kadar_asap']) && !empty($_POST['status']) && !empty($_POST['lokasi']))
	{
		$suhu = $_POST['suhu'];
		$kadar_asap = $_POST['kadar_asap'];
		$status = $_POST['status'];
		$lokasi = $_POST['lokasi']; // Ambil data lokasi dari form

		$sql = "INSERT INTO tbl_data (suhu, kadar_asap, status, lokasi)
		VALUES ('".$suhu."', '".$kadar_asap."', '".$status."', '".$lokasi."')";

		if ($conn->query($sql) === TRUE) {
			echo "Data berhasil tersimpan.";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	} else {
		echo "Harap isi semua data!";
	}

	$conn->close();
?>


