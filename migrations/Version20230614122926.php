<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230614122926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE society (id INT AUTO_INCREMENT NOT NULL, society_name VARCHAR(255) NOT NULL, society_description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE smartphone ADD society_id INT NOT NULL');
        $this->addSql('ALTER TABLE smartphone ADD CONSTRAINT FK_26B07E2EE6389D24 FOREIGN KEY (society_id) REFERENCES society (id)');
        $this->addSql('CREATE INDEX IDX_26B07E2EE6389D24 ON smartphone (society_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE smartphone DROP FOREIGN KEY FK_26B07E2EE6389D24');
        $this->addSql('DROP TABLE society');
        $this->addSql('DROP INDEX IDX_26B07E2EE6389D24 ON smartphone');
        $this->addSql('ALTER TABLE smartphone DROP society_id');
    }
}
