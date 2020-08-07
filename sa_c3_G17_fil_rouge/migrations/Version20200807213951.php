<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200807213951 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE groupe_tag (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, is_deleted TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groupe_tag_tag (groupe_tag_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_C430CACFD1EC9F2B (groupe_tag_id), INDEX IDX_C430CACFBAD26311 (tag_id), PRIMARY KEY(groupe_tag_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groupes (id INT AUTO_INCREMENT NOT NULL, promos_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, date_creation DATE NOT NULL, statut VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_576366D9CAA392D2 (promos_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groupes_formateur (groupes_id INT NOT NULL, formateur_id INT NOT NULL, INDEX IDX_9481F39E305371B (groupes_id), INDEX IDX_9481F39E155D8F51 (formateur_id), PRIMARY KEY(groupes_id, formateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groupes_apprenant (groupes_id INT NOT NULL, apprenant_id INT NOT NULL, INDEX IDX_BD1CCBFF305371B (groupes_id), INDEX IDX_BD1CCBFFC5697D6D (apprenant_id), PRIMARY KEY(groupes_id, apprenant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE niveau (id INT AUTO_INCREMENT NOT NULL, competence_id INT NOT NULL, libelle VARCHAR(255) NOT NULL, critere_evaluation VARCHAR(255) NOT NULL, groupe_action VARCHAR(255) NOT NULL, is_deleted TINYINT(1) NOT NULL, INDEX IDX_4BDFF36B15761DAB (competence_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE promos (id INT AUTO_INCREMENT NOT NULL, referentiel_id INT DEFAULT NULL, admin_id INT DEFAULT NULL, langue VARCHAR(255) NOT NULL, titre VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, lieu VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin_provisoire DATE NOT NULL, fabrique VARCHAR(255) NOT NULL, date_fin_reelle DATE NOT NULL, etat TINYINT(1) NOT NULL, is_deleted TINYINT(1) NOT NULL, INDEX IDX_31D1F705805DB139 (referentiel_id), INDEX IDX_31D1F705642B8210 (admin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE promos_formateur (promos_id INT NOT NULL, formateur_id INT NOT NULL, INDEX IDX_70F76221CAA392D2 (promos_id), INDEX IDX_70F76221155D8F51 (formateur_id), PRIMARY KEY(promos_id, formateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referentiel (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, presentation VARCHAR(255) NOT NULL, programme VARCHAR(255) NOT NULL, critere_admission VARCHAR(255) NOT NULL, critere_evaluation VARCHAR(255) NOT NULL, is_deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referentiel_groupe_competence (referentiel_id INT NOT NULL, groupe_competence_id INT NOT NULL, INDEX IDX_EC387D5B805DB139 (referentiel_id), INDEX IDX_EC387D5B89034830 (groupe_competence_id), PRIMARY KEY(referentiel_id, groupe_competence_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, descriptif VARCHAR(255) NOT NULL, is_deleted TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE groupe_tag_tag ADD CONSTRAINT FK_C430CACFD1EC9F2B FOREIGN KEY (groupe_tag_id) REFERENCES groupe_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groupe_tag_tag ADD CONSTRAINT FK_C430CACFBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groupes ADD CONSTRAINT FK_576366D9CAA392D2 FOREIGN KEY (promos_id) REFERENCES promos (id)');
        $this->addSql('ALTER TABLE groupes_formateur ADD CONSTRAINT FK_9481F39E305371B FOREIGN KEY (groupes_id) REFERENCES groupes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groupes_formateur ADD CONSTRAINT FK_9481F39E155D8F51 FOREIGN KEY (formateur_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groupes_apprenant ADD CONSTRAINT FK_BD1CCBFF305371B FOREIGN KEY (groupes_id) REFERENCES groupes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groupes_apprenant ADD CONSTRAINT FK_BD1CCBFFC5697D6D FOREIGN KEY (apprenant_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE niveau ADD CONSTRAINT FK_4BDFF36B15761DAB FOREIGN KEY (competence_id) REFERENCES competence (id)');
        $this->addSql('ALTER TABLE promos ADD CONSTRAINT FK_31D1F705805DB139 FOREIGN KEY (referentiel_id) REFERENCES referentiel (id)');
        $this->addSql('ALTER TABLE promos ADD CONSTRAINT FK_31D1F705642B8210 FOREIGN KEY (admin_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE promos_formateur ADD CONSTRAINT FK_70F76221CAA392D2 FOREIGN KEY (promos_id) REFERENCES promos (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE promos_formateur ADD CONSTRAINT FK_70F76221155D8F51 FOREIGN KEY (formateur_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE referentiel_groupe_competence ADD CONSTRAINT FK_EC387D5B805DB139 FOREIGN KEY (referentiel_id) REFERENCES referentiel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE referentiel_groupe_competence ADD CONSTRAINT FK_EC387D5B89034830 FOREIGN KEY (groupe_competence_id) REFERENCES groupe_competence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE competence ADD libelle VARCHAR(255) NOT NULL, ADD descriptif VARCHAR(255) NOT NULL, DROP libelle_c, DROP descriptif_c');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE groupe_tag_tag DROP FOREIGN KEY FK_C430CACFD1EC9F2B');
        $this->addSql('ALTER TABLE groupes_formateur DROP FOREIGN KEY FK_9481F39E305371B');
        $this->addSql('ALTER TABLE groupes_apprenant DROP FOREIGN KEY FK_BD1CCBFF305371B');
        $this->addSql('ALTER TABLE groupes DROP FOREIGN KEY FK_576366D9CAA392D2');
        $this->addSql('ALTER TABLE promos_formateur DROP FOREIGN KEY FK_70F76221CAA392D2');
        $this->addSql('ALTER TABLE promos DROP FOREIGN KEY FK_31D1F705805DB139');
        $this->addSql('ALTER TABLE referentiel_groupe_competence DROP FOREIGN KEY FK_EC387D5B805DB139');
        $this->addSql('ALTER TABLE groupe_tag_tag DROP FOREIGN KEY FK_C430CACFBAD26311');
        $this->addSql('DROP TABLE groupe_tag');
        $this->addSql('DROP TABLE groupe_tag_tag');
        $this->addSql('DROP TABLE groupes');
        $this->addSql('DROP TABLE groupes_formateur');
        $this->addSql('DROP TABLE groupes_apprenant');
        $this->addSql('DROP TABLE niveau');
        $this->addSql('DROP TABLE promos');
        $this->addSql('DROP TABLE promos_formateur');
        $this->addSql('DROP TABLE referentiel');
        $this->addSql('DROP TABLE referentiel_groupe_competence');
        $this->addSql('DROP TABLE tag');
        $this->addSql('ALTER TABLE competence ADD libelle_c VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD descriptif_c VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP libelle, DROP descriptif');
    }
}
