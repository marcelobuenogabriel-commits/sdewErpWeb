<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobFinalizadoNotification extends Notification
{
    use Queueable;


    protected $status;
    protected $numPro;
    protected $codFam;
    protected $station;

    public function __construct($status, $numPro, $codFam, $station = NULL)
    {
        $this->status = $status;
        $this->numPro = $numPro;
	$this->codFam = $codFam;
	$this->station = $station;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Job Finalizado: ' . $this->numPro)
            ->line("O job para o projeto  {$this->numPro} e {$this->codFam} e Station {$this->station} foi finalizado com status: {$this->status}.")
            ->line('Verifique os resultados no sistema.');
    }
}
