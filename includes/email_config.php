<?php
// Charger PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    
    // Configuration SMTP
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_USER = 'kocakerim98@gmail.com';
    const SMTP_PASS = 'folabhbgvgitq xpo'; // TON MOT DE PASSE D'APPLICATION ICI
    const FROM_EMAIL = 'kocakerim98@gmail.com';
    const FROM_NAME = 'DIAMON Luxe';
    
    /**
     * Configure et retourne une instance PHPMailer
     */
    private static function getMailer() {
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host       = self::SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = self::SMTP_USER;
            $mail->Password   = self::SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = self::SMTP_PORT;
            
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->setFrom(self::FROM_EMAIL, self::FROM_NAME);
            
            return $mail;
            
        } catch (Exception $e) {
            error_log("Erreur configuration PHPMailer: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Envoie un email de réinitialisation de mot de passe
     */
    public static function envoyerResetPassword($email, $token, $nom) {
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            $mail->addAddress($email, $nom);
            $mail->Subject = 'Réinitialisation de votre mot de passe - DIAMON';
            
            $reset_link = "http://localhost/diamon_luxe/reset_password.php?token=" . $token;
            
            $mail->isHTML(true);
            $mail->Body = self::getTemplateResetPassword($nom, $reset_link);
            $mail->AltBody = "Bonjour $nom,\n\nVous avez demandé à réinitialiser votre mot de passe.\nCliquez sur ce lien : $reset_link\n\nCe lien est valable 1 heure.";
            
            return $mail->send();
            
        } catch (Exception $e) {
            error_log("Erreur envoi email reset: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Envoie une notification complète de changement de statut de vente
     */
    public static function notifierStatutVente($email, $nom, $demande_id, $statut, $estimation = null, $commentaire = null, $details_produit = null) {
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            $mail->addAddress($email, $nom);
            $mail->Subject = "Mise à jour de votre demande de vente #$demande_id - DIAMON";
            
            $mail->isHTML(true);
            $mail->Body = self::getTemplateStatutVente($nom, $demande_id, $statut, $estimation, $commentaire, $details_produit);
            
            return $mail->send();
            
        } catch (Exception $e) {
            error_log("Erreur envoi notification vente: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Envoie une notification complète de changement de statut d'échange
     */
    public static function notifierStatutEchange($email, $nom, $demande_id, $statut, $evaluation = null, $difference = null, $commentaire = null, $details_echange = null) {
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            $mail->addAddress($email, $nom);
            $mail->Subject = "Mise à jour de votre demande d'échange #$demande_id - DIAMON";
            
            $mail->isHTML(true);
            $mail->Body = self::getTemplateStatutEchange($nom, $demande_id, $statut, $evaluation, $difference, $commentaire, $details_echange);
            
            return $mail->send();
            
        } catch (Exception $e) {
            error_log("Erreur envoi notification échange: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Template HTML - Réinitialisation mot de passe
     */
    private static function getTemplateResetPassword($nom, $reset_link) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: #000; color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 32px; letter-spacing: 4px; }
                .content { padding: 40px 30px; }
                .content h2 { color: #C5A059; margin-bottom: 20px; }
                .content p { line-height: 1.6; color: #555; margin-bottom: 20px; }
                .btn { display: inline-block; background: #C5A059; color: white !important; padding: 15px 40px; text-decoration: none; border-radius: 4px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; font-size: 12px; }
                .footer { background: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #999; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; color: #856404; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>DIAMON</h1>
                </div>
                <div class='content'>
                    <h2>Réinitialisation de mot de passe</h2>
                    <p>Bonjour <strong>$nom</strong>,</p>
                    <p>Vous avez demandé à réinitialiser votre mot de passe sur DIAMON.</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='$reset_link' class='btn'>Réinitialiser mon mot de passe</a>
                    </p>
                    <div class='warning'>
                        <strong>⚠️ Important :</strong> Ce lien est valable pendant <strong>1 heure</strong> uniquement.
                    </div>
                    <p><small>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</small></p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " DIAMON LUXE - Tous droits réservés</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Template HTML COMPLET - Notification statut vente
     */
    private static function getTemplateStatutVente($nom, $demande_id, $statut, $estimation, $commentaire, $details_produit) {
        $statut_info = [
            'en attente' => [
                'titre' => 'Demande Reçue ⏳', 
                'couleur' => '#ffc107', 
                'message' => 'Votre demande de vente a bien été reçue et est en cours d\'examen par nos experts.',
                'actions' => 'Nos experts vont analyser votre article sous 48h ouvrées.'
            ],
            'en cours d\'évaluation' => [
                'titre' => 'Expertise en Cours 🔍', 
                'couleur' => '#2196F3', 
                'message' => 'Nos experts sont actuellement en train d\'évaluer votre article.',
                'actions' => 'Vous recevrez très prochainement notre estimation détaillée.'
            ],
            'acceptée' => [
                'titre' => 'Demande Acceptée ! 🎉', 
                'couleur' => '#4CAF50', 
                'message' => 'Excellente nouvelle ! Votre article a été accepté par nos experts.',
                'actions' => 'Notre équipe va vous contacter sous 24h pour finaliser la transaction.'
            ],
            'refusée' => [
                'titre' => 'Demande Refusée ❌', 
                'couleur' => '#f44336', 
                'message' => 'Malheureusement, nous ne pouvons pas accepter votre article pour le moment.',
                'actions' => 'N\'hésitez pas à nous proposer d\'autres articles de votre collection.'
            ],
            'vendue' => [
                'titre' => 'Article Vendu ! ✨', 
                'couleur' => '#9C27B0', 
                'message' => 'Félicitations ! Votre article a été vendu avec succès.',
                'actions' => 'Le paiement sera effectué sous 72h sur votre compte bancaire.'
            ]
        ];
        
        $info = $statut_info[$statut] ?? ['titre' => 'Mise à jour', 'couleur' => '#666', 'message' => 'Votre demande a été mise à jour.', 'actions' => ''];
        
        // Détails du produit
        $produit_html = '';
        if ($details_produit) {
            $produit_html = "
            <div style='background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin: 20px 0;'>
                <h3 style='margin: 0 0 15px 0; color: #333; font-size: 16px;'>📦 Détails de votre article</h3>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr>
                        <td style='padding: 8px 0; color: #666; font-size: 13px;'><strong>Marque :</strong></td>
                        <td style='padding: 8px 0; color: #333; font-size: 13px; text-align: right;'>" . htmlspecialchars($details_produit['marque']) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; color: #666; font-size: 13px;'><strong>Produit :</strong></td>
                        <td style='padding: 8px 0; color: #333; font-size: 13px; text-align: right;'>" . htmlspecialchars($details_produit['nom_produit']) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; color: #666; font-size: 13px;'><strong>Catégorie :</strong></td>
                        <td style='padding: 8px 0; color: #333; font-size: 13px; text-align: right;'>" . htmlspecialchars($details_produit['categorie']) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; color: #666; font-size: 13px;'><strong>État estimé :</strong></td>
                        <td style='padding: 8px 0; color: #333; font-size: 13px; text-align: right;'>Grade " . htmlspecialchars($details_produit['etat_estime']) . "</td>
                    </tr>
                </table>
            </div>
            ";
        }
        
        $estimation_html = '';
        if ($estimation) {
            $estimation_html = "
            <div style='background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-left: 5px solid #4CAF50; padding: 25px; margin: 25px 0; border-radius: 8px;'>
                <div style='display: flex; align-items: center; margin-bottom: 15px;'>
                    <div style='font-size: 32px; margin-right: 15px;'>💎</div>
                    <div>
                        <h3 style='margin: 0; color: #2e7d32; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;'>Estimation Expert</h3>
                    </div>
                </div>
                <p style='font-size: 42px; font-weight: bold; margin: 10px 0; color: #1b5e20; font-family: Georgia, serif;'>" . number_format($estimation, 0, ',', ' ') . " €</p>
                <p style='margin: 10px 0 0 0; color: #558b2f; font-size: 13px;'>Estimation réalisée par nos experts certifiés</p>
            </div>
            ";
        }
        
        $commentaire_html = '';
        if ($commentaire) {
            $commentaire_html = "
            <div style='background: #f5f5f5; border-left: 4px solid #C5A059; padding: 20px; border-radius: 4px; margin: 20px 0;'>
                <h4 style='margin: 0 0 12px 0; color: #666; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;'>💬 Commentaire de l'expert</h4>
                <p style='margin: 0; color: #555; line-height: 1.7; font-size: 14px;'>" . nl2br(htmlspecialchars($commentaire)) . "</p>
            </div>
            ";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
                .header { background: #000; color: white; padding: 35px; text-align: center; }
                .header h1 { margin: 0; font-size: 36px; letter-spacing: 5px; font-weight: 300; }
                .status-badge { background: {$info['couleur']}; color: white; padding: 12px 25px; border-radius: 25px; display: inline-block; margin: 25px 0; font-weight: bold; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; }
                .content { padding: 40px 30px; }
                .content h2 { color: #C5A059; margin-bottom: 15px; font-size: 22px; }
                .content p { line-height: 1.7; color: #555; margin-bottom: 15px; font-size: 15px; }
                .btn { display: inline-block; background: #C5A059; color: white !important; padding: 18px 45px; text-decoration: none; border-radius: 5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; font-size: 12px; margin: 20px 0; }
                .btn:hover { background: #a68845; }
                .footer { background: #f9f9f9; padding: 25px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #e0e0e0; }
                .info-box { background: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>DIAMON</h1>
                    <p style='margin: 10px 0 0 0; font-size: 12px; letter-spacing: 2px; opacity: 0.8;'>L'EXCELLENCE CERTIFIÉE</p>
                </div>
                <div class='content'>
                    <div style='text-align: center;'>
                        <div class='status-badge'>{$info['titre']}</div>
                    </div>
                    <h2>Demande de Vente #{$demande_id}</h2>
                    <p>Bonjour <strong>$nom</strong>,</p>
                    <p>{$info['message']}</p>
                    
                    $produit_html
                    $estimation_html
                    $commentaire_html
                    
                    <div class='info-box'>
                        <p style='margin: 0; color: #1565c0; font-size: 14px;'><strong>📋 Prochaines étapes :</strong><br>{$info['actions']}</p>
                    </div>
                    
                    <div style='text-align: center; margin: 35px 0;'>
                        <a href='http://localhost/diamon_luxe/compte.php' class='btn'>Voir ma Demande</a>
                    </div>
                    
                    <div style='background: #fff3e0; border-radius: 8px; padding: 20px; margin: 25px 0;'>
                        <h4 style='margin: 0 0 10px 0; color: #e65100; font-size: 14px;'>📞 Besoin d'aide ?</h4>
                        <p style='margin: 0; font-size: 13px; color: #666;'>
                            Notre service client est à votre disposition :<br>
                            <strong>Email :</strong> kocakerim98@gmail.com<br>
                            <strong>Du lundi au vendredi :</strong> 9h - 18h
                        </p>
                    </div>
                </div>
                <div class='footer'>
                    <p style='margin: 0 0 10px 0;'>&copy; " . date('Y') . " DIAMON LUXE HOLDING - Tous droits réservés</p>
                    <p style='margin: 0;'>L'excellence certifiée depuis 2024</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Template HTML COMPLET - Notification statut échange
     */
    private static function getTemplateStatutEchange($nom, $demande_id, $statut, $evaluation, $difference, $commentaire, $details_echange) {
        $statut_info = [
            'en attente' => [
                'titre' => 'Demande Reçue ⏳', 
                'couleur' => '#ffc107',
                'message' => 'Votre proposition d\'échange a bien été reçue.',
                'actions' => 'Nos experts vont analyser votre article et l\'article souhaité sous 48h.'
            ],
            'en cours d\'évaluation' => [
                'titre' => 'Expertise en Cours 🔍', 
                'couleur' => '#2196F3',
                'message' => 'Nos experts évaluent actuellement votre article pour l\'échange.',
                'actions' => 'Vous recevrez notre évaluation détaillée très prochainement.'
            ],
            'acceptée' => [
                'titre' => 'Échange Accepté ! 🎉', 
                'couleur' => '#4CAF50',
                'message' => 'Excellente nouvelle ! Votre échange a été accepté par nos experts.',
                'actions' => 'Notre équipe va vous contacter sous 24h pour organiser l\'échange.'
            ],
            'refusée' => [
                'titre' => 'Échange Refusé ❌', 
                'couleur' => '#f44336',
                'message' => 'Malheureusement, nous ne pouvons pas accepter cet échange.',
                'actions' => 'N\'hésitez pas à nous proposer d\'autres articles de votre collection.'
            ],
            'échange effectué' => [
                'titre' => 'Échange Effectué ! ✨', 
                'couleur' => '#9C27B0',
                'message' => 'Félicitations ! L\'échange a été effectué avec succès.',
                'actions' => 'Vous allez recevoir votre nouvel article sous 48h.'
            ]
        ];
        
        $info = $statut_info[$statut] ?? ['titre' => 'Mise à jour', 'couleur' => '#666', 'message' => 'Votre demande a été mise à jour.', 'actions' => ''];
        
        // Détails de l'échange
        $echange_html = '';
        if ($details_echange) {
            $echange_html = "
            <div style='margin: 30px 0;'>
                <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;'>
                    <!-- Article proposé -->
                    <div style='background: #e3f2fd; border: 2px solid #2196F3; border-radius: 8px; padding: 20px;'>
                        <h4 style='margin: 0 0 15px 0; color: #1565c0; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>📤 Votre Article</h4>
                        <p style='margin: 5px 0; color: #333; font-size: 14px;'><strong>" . htmlspecialchars($details_echange['marque_propose']) . "</strong></p>
                        <p style='margin: 5px 0; color: #666; font-size: 13px;'>" . htmlspecialchars($details_echange['nom_produit_propose']) . "</p>
                        <p style='margin: 10px 0 0 0; color: #555; font-size: 12px;'>Grade " . htmlspecialchars($details_echange['etat_estime']) . "</p>
                    </div>
                    
                    <!-- Article souhaité -->
                    <div style='background: #fff3e0; border: 2px solid #ff9800; border-radius: 8px; padding: 20px;'>
                        <h4 style='margin: 0 0 15px 0; color: #e65100; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;'>📥 Article Souhaité</h4>
                        <p style='margin: 5px 0; color: #333; font-size: 14px;'><strong>" . htmlspecialchars($details_echange['produit_souhaite_marque']) . "</strong></p>
                        <p style='margin: 5px 0; color: #666; font-size: 13px;'>" . htmlspecialchars($details_echange['produit_souhaite_nom']) . "</p>
                        <p style='margin: 10px 0 0 0; color: #C5A059; font-size: 16px; font-weight: bold;'>" . number_format($details_echange['produit_souhaite_prix'], 0, ',', ' ') . " €</p>
                    </div>
                </div>
            </div>
            ";
        }
        
        $evaluation_html = '';
        if ($evaluation) {
            $evaluation_html = "
            <div style='background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 5px solid #2196F3; padding: 25px; margin: 25px 0; border-radius: 8px;'>
                <div style='display: flex; align-items: center; margin-bottom: 15px;'>
                    <div style='font-size: 32px; margin-right: 15px;'>💎</div>
                    <div>
                        <h3 style='margin: 0; color: #1565c0; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;'>Évaluation de votre article</h3>
                    </div>
                </div>
                <p style='font-size: 42px; font-weight: bold; margin: 10px 0; color: #0d47a1; font-family: Georgia, serif;'>" . number_format($evaluation, 0, ',', ' ') . " €</p>
                <p style='margin: 10px 0 0 0; color: #1976d2; font-size: 13px;'>Estimation réalisée par nos experts certifiés</p>
            </div>
            ";
        }
        
        $difference_html = '';
        if ($difference !== null) {
            if ($difference > 0) {
                $difference_html = "
                <div style='background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border-left: 5px solid #ff9800; padding: 25px; margin: 25px 0; border-radius: 8px;'>
                    <div style='display: flex; align-items: center; margin-bottom: 15px;'>
                        <div style='font-size: 32px; margin-right: 15px;'>💰</div>
                        <div>
                            <h3 style='margin: 0; color: #e65100; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;'>Complément à verser</h3>
                        </div>
                    </div>
                    <p style='font-size: 42px; font-weight: bold; margin: 10px 0; color: #e65100; font-family: Georgia, serif;'>+ " . number_format($difference, 0, ',', ' ') . " €</p>
                    <p style='margin: 10px 0 0 0; color: #f57c00; font-size: 13px;'>Vous devrez compléter cette somme pour finaliser l\'échange</p>
                </div>
                ";
            } elseif ($difference < 0) {
                $difference_html = "
                <div style='background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-left: 5px solid #4CAF50; padding: 25px; margin: 25px 0; border-radius: 8px;'>
                    <div style='display: flex; align-items: center; margin-bottom: 15px;'>
                        <div style='font-size: 32px; margin-right: 15px;'>💚</div>
                        <div>
                            <h3 style='margin: 0; color: #2e7d32; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;'>Crédit en votre faveur</h3>
                        </div>
                    </div>
                    <p style='font-size: 42px; font-weight: bold; margin: 10px 0; color: #2e7d32; font-family: Georgia, serif;'>" . number_format(abs($difference), 0, ',', ' ') . " €</p>
                    <p style='margin: 10px 0 0 0; color: #388e3c; font-size: 13px;'>Cette somme vous sera créditée ou remboursée</p>
                </div>
                ";
            } else {
                $difference_html = "
                <div style='background: #f5f5f5; border-left: 5px solid #9e9e9e; padding: 25px; margin: 25px 0; border-radius: 8px;'>
                    <div style='display: flex; align-items: center; margin-bottom: 15px;'>
                        <div style='font-size: 32px; margin-right: 15px;'>⚖️</div>
                        <div>
                            <h3 style='margin: 0; color: #555; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;'>Échange équitable</h3>
                        </div>
                    </div>
                    <p style='margin: 10px 0 0 0; color: #666; font-size: 14px;'>Les deux articles ont une valeur équivalente. Aucun complément à verser.</p>
                </div>
                ";
            }
        }
        
        $commentaire_html = '';
        if ($commentaire) {
            $commentaire_html = "
            <div style='background: #f5f5f5; border-left: 4px solid #C5A059; padding: 20px; border-radius: 4px; margin: 20px 0;'>
                <h4 style='margin: 0 0 12px 0; color: #666; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;'>💬 Commentaire de l'expert</h4>
                <p style='margin: 0; color: #555; line-height: 1.7; font-size: 14px;'>" . nl2br(htmlspecialchars($commentaire)) . "</p>
            </div>
            ";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
                .header { background: #000; color: white; padding: 35px; text-align: center; }
                .header h1 { margin: 0; font-size: 36px; letter-spacing: 5px; font-weight: 300; }
                .status-badge { background: {$info['couleur']}; color: white; padding: 12px 25px; border-radius: 25px; display: inline-block; margin: 25px 0; font-weight: bold; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; }
                .content { padding: 40px 30px; }
                .content h2 { color: #C5A059; margin-bottom: 15px; font-size: 22px; }
                .content p { line-height: 1.7; color: #555; margin-bottom: 15px; font-size: 15px; }
                .btn { display: inline-block; background: #C5A059; color: white !important; padding: 18px 45px; text-decoration: none; border-radius: 5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; font-size: 12px; margin: 20px 0; }
                .btn:hover { background: #a68845; }
                .footer { background: #f9f9f9; padding: 25px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #e0e0e0; }
                .info-box { background: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>DIAMON</h1>
                    <p style='margin: 10px 0 0 0; font-size: 12px; letter-spacing: 2px; opacity: 0.8;'>L'EXCELLENCE CERTIFIÉE</p>
                </div>
                <div class='content'>
                    <div style='text-align: center;'>
                        <div class='status-badge'>{$info['titre']}</div>
                    </div>
                    <h2>Demande d'Échange #{$demande_id}</h2>
                    <p>Bonjour <strong>$nom</strong>,</p>
                    <p>{$info['message']}</p>
                    
                    $echange_html
                    $evaluation_html
                    $difference_html
                    $commentaire_html
                    
                    <div class='info-box'>
                        <p style='margin: 0; color: #1565c0; font-size: 14px;'><strong>📋 Prochaines étapes :</strong><br>{$info['actions']}</p>
                    </div>
                    
                    <div style='text-align: center; margin: 35px 0;'>
                        <a href='http://localhost/diamon_luxe/compte.php' class='btn'>Voir ma Demande</a>
                    </div>
                    
                    <div style='background: #fff3e0; border-radius: 8px; padding: 20px; margin: 25px 0;'>
                        <h4 style='margin: 0 0 10px 0; color: #e65100; font-size: 14px;'>📞 Besoin d'aide ?</h4>
                        <p style='margin: 0; font-size: 13px; color: #666;'>
                            Notre service client est à votre disposition :<br>
                            <strong>Email :</strong> kocakerim98@gmail.com<br>
                            <strong>Du lundi au vendredi :</strong> 9h - 18h
                        </p>
                    </div>
                </div>
                <div class='footer'>
                    <p style='margin: 0 0 10px 0;'>&copy; " . date('Y') . " DIAMON LUXE HOLDING - Tous droits réservés</p>
                    <p style='margin: 0;'>L'excellence certifiée depuis 2024</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}