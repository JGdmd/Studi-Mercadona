<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230715154205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP CONSTRAINT fk_d34a04ad295a716c');
        $this->addSql('DROP SEQUENCE image_product_id_seq CASCADE');
        $this->addSql('DROP TABLE image_product');
        $this->addSql('DROP INDEX uniq_d34a04ad295a716c');
        $this->addSql('ALTER TABLE product ADD image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE product DROP image_product_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE image_product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE image_product (id INT NOT NULL, image_name VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN image_product.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE product ADD image_product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product DROP image');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT fk_d34a04ad295a716c FOREIGN KEY (image_product_id) REFERENCES image_product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_d34a04ad295a716c ON product (image_product_id)');
    }
}
