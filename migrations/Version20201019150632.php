<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201019150632 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, external_id INT NOT NULL, provider VARCHAR(32) NOT NULL, UNIQUE INDEX UK_TYPE_EXTERNAL_ID (external_id, provider), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, slack_notification_path LONGTEXT DEFAULT NULL, labels LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team_project (team_id INT NOT NULL, project_id INT NOT NULL, INDEX IDX_D0CAA1D9296CD8AE (team_id), INDEX IDX_D0CAA1D9166D1F9C (project_id), PRIMARY KEY(team_id, project_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE team_project ADD CONSTRAINT FK_D0CAA1D9296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_project ADD CONSTRAINT FK_D0CAA1D9166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE team_project DROP FOREIGN KEY FK_D0CAA1D9166D1F9C');
        $this->addSql('ALTER TABLE team_project DROP FOREIGN KEY FK_D0CAA1D9296CD8AE');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE team_project');
    }
}
