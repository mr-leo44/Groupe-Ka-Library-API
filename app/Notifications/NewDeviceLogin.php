<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDeviceLoginNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $ip,
        private string $userAgent
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $deviceInfo = $this->parseUserAgent();
        $location = $this->getLocationFromIp(); // Optional: implement IP geolocation

        return (new MailMessage)
            ->subject('🔐 Nouvelle connexion détectée - Groupe Ka Library')
            ->greeting("Bonjour {$notifiable->name},")
            ->line('Une connexion à votre compte a été détectée depuis un nouvel appareil ou une nouvelle adresse IP.')
            ->line('')
            ->line("**📱 Appareil :** {$deviceInfo}")
            ->line("**🌍 Adresse IP :** {$this->ip}")
            ->line("**📅 Date :** " . now()->format('d/m/Y à H:i:s'))
            ->line('')
            ->line("**Si c'était vous :** Aucune action n'est requise. Votre compte est sécurisé.")
            ->line("**Si ce n'était pas vous :** Changez immédiatement votre mot de passe et contactez notre support.")
            ->action('Changer mon mot de passe', url(env('FRONTEND_URL') . '/forgot-password'))
            ->line('')
            ->line('Pour votre sécurité, nous vous recommandons de :')
            ->line('• Utiliser un mot de passe unique et fort')
            ->line('• Activer l\'authentification à deux facteurs (bientôt disponible)')
            ->line('• Ne jamais partager vos identifiants')
            ->line('')
            ->line('Merci de votre vigilance !')
            ->salutation('L\'équipe Groupe Ka Library');
    }

    /**
     * Parse user agent to human-readable device type
     */
    private function parseUserAgent(): string
    {
        $ua = strtolower($this->userAgent);

        // Mobile devices
        if (str_contains($ua, 'iphone')) return '📱 iPhone';
        if (str_contains($ua, 'ipad')) return '📱 iPad';
        if (str_contains($ua, 'android') && str_contains($ua, 'mobile')) return '📱 Android Mobile';
        if (str_contains($ua, 'android')) return '📱 Tablette Android';
        
        // Browsers on desktop
        if (str_contains($ua, 'windows')) return '💻 Ordinateur Windows';
        if (str_contains($ua, 'macintosh') || str_contains($ua, 'mac os')) return '💻 Mac';
        if (str_contains($ua, 'linux')) return '💻 Ordinateur Linux';
        
        return '🖥️ Appareil inconnu';
    }

    /**
     * Get approximate location from IP (optional enhancement)
     * Requires a geolocation service like ipapi.co, ipstack, etc.
     */
    private function getLocationFromIp(): ?string
    {
        // Implementation example (requires external API):
        // try {
        //     $response = Http::get("https://ipapi.co/{$this->ip}/json/");
        //     $data = $response->json();
        //     return "{$data['city']}, {$data['country_name']}";
        // } catch (\Exception $e) {
        //     return null;
        // }
        
        return null; // Not implemented by default
    }
}
