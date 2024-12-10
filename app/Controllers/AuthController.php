<?php
class AuthController {
    private $auth;
    
    public function __construct(Auth $auth) {
        $this->auth = $auth;
    }

    public function login() {
        include "views/auth/login.php";
    }

    public function otp() {
        include "views/auth/otp.php";
    }
    
    public function actionlogin() {
        
        $nim = $_POST['nim'];
        $password = $_POST['password'];
        
        try {
            $user = $this->auth->authenticate($nim, $password);
            
            if ($user) {
                $otp = str_pad(rand(0,999999),6, '0', STR_PAD_LEFT);
                $_SESSION['temp_auth'] = [
                    'user' => $user,
                    'otp' => $otp,
                    'expires' => time() + (5*60)
                ];
                $this->sendOTPEmail($user['EMAIL'],$otp);
                
                return json_encode([
                    "status" => "success",
                    "message" => "Login successful"
                ]);
            } else {
                return json_encode([
                    "status" => "error",
                    "message" => "NIM atau password salah"
                ]);
            }
            
        } catch (Exception $e) {
            return json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function verifyOTP() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $inputOTP = $_POST['otp'];

            if (!isset($_SESSION['temp_auth'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Sesi verifikasi tidak valid"
                ]);
                return;
            }

            $tempAuth = $_SESSION['temp_auth'];

            if (time() > $tempAuth['expires']) {
                unset($_SESSION['temp_auth']);
                echo json_encode([
                    "status" => "error",
                    "message" => "OTP sudah kadaluarsa"
                ]);
                return;
            }

            if ($inputOTP === $tempAuth['otp']) {
                $user = $tempAuth['user'];
                $jurusan = $this->auth->getJurusan($user['ID']);
         
                if ($jurusan === null) {
                    error_log("Jurusan tidak ditemukan untuk ID: " . $user['ID']);
                }
         
                $_SESSION['user'] = [
                    'nim' => $user['NIM'],
                    'username' => $user['USERNAME'],
                    'role' => $user['ROLE'],
                    'id' => $user['ID'],
                    'image' => $user['image'],
                    'email' => $user['EMAIL'],
                    'no_hp' => $user['NO_HP'],
                    'alamat' => $user['ALAMAT'],
                    'jenis_kelamin' => $user['JENIS_KELAMIN'],
                    'tempat_lahir' => $user['tempat_lahir'],
                    'tanggal_lahir' => $user['tanggal_lahir'],
                    'jurusan' => $jurusan,
                ];
         
                echo json_encode([
                    "status" => "success",
                    "role" => $user['ROLE'] 
                ]);
         
                unset($_SESSION['temp_auth']);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "OTP tidak valid"
                ]);
            }
        }
    }

    public function sendOTPEmail($email,$otp){
        $subject = 'Verifikasi OTP';
        $message1 = "Kode verifikasi OTP Anda adalah: $otp. Silahkan masukkan kode ini ke formulir verifikasi OTP di halaman login.";
        $message2 = "Jangan memberikan kode ini pada siapapun, Kode berlaku selama 5 menit.";
        mail($email, $subject, $message1 . $message2);
    }
    
}