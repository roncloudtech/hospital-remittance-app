<?
namespace App\Mail;

use App\Models\Hospital;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewHospitalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $hospital;

    public function __construct(Hospital $hospital, $email, $name)
    {
        $this->name = $name;
        $this->email = $email;
        $this->hospital = $hospital;
    }

    public function build()
    {
        return $this->view('emails.new-hospital-notification');
    }
}
