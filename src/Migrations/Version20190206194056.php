<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190206194056 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'First migration to be apply on tutoriux v2 schema.';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE media DROP CONSTRAINT fk_6a2ca10c7987212d');
        $this->addSql('ALTER TABLE navigation DROP CONSTRAINT fk_493ac53f7987212d');
        $this->addSql('ALTER TABLE section DROP CONSTRAINT fk_2d737aef7987212d');
        $this->addSql('ALTER TABLE media_folder DROP CONSTRAINT fk_50db93137987212d');
        $this->addSql('ALTER TABLE mapping DROP CONSTRAINT fk_49e62c8a7987212d');
        $this->addSql('ALTER TABLE token_translation DROP CONSTRAINT fk_985817f541dee7b9');
        $this->addSql('DROP SEQUENCE app_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE container_config_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE container_extension_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE container_parameter_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE token_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE token_translation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_id_seq CASCADE');
        $this->addSql('DROP TABLE app');
        $this->addSql('DROP TABLE token_translation');
        $this->addSql('DROP TABLE token');
        $this->addSql('DROP INDEX idx_49e62c8a7987212d');
        $this->addSql('ALTER TABLE mapping DROP app_id');
        $this->addSql('ALTER TABLE mapping DROP cms');
        $this->addSql('DROP INDEX idx_2d737aef7987212d');
        $this->addSql('ALTER TABLE section DROP app_id');
        $this->addSql('DROP INDEX idx_493ac53f7987212d');
        $this->addSql('ALTER TABLE navigation DROP app_id');
        $this->addSql('ALTER TABLE text RENAME COLUMN collapsable TO collapsible');
        $this->addSql('DROP INDEX idx_50db93137987212d');
        $this->addSql('ALTER TABLE media_folder DROP app_id');
        $this->addSql('DROP INDEX idx_6a2ca10c7987212d');
        $this->addSql('ALTER TABLE media DROP app_id');
        $this->addSql('ALTER TABLE request ALTER checklist TYPE TEXT');
        $this->addSql('ALTER TABLE request ALTER checklist DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN request.checklist IS NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE app_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE container_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE container_extension_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE container_parameter_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE token_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE app (id INT NOT NULL, name VARCHAR(255) NOT NULL, prefix VARCHAR(255) DEFAULT NULL, ordering INT DEFAULT NULL, slug VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE token_translation (id INT NOT NULL, token_id INT DEFAULT NULL, locale VARCHAR(2) NOT NULL, name VARCHAR(200) NOT NULL, domain VARCHAR(200) NOT NULL, active BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_985817f541dee7b9 ON token_translation (token_id)');
        $this->addSql('CREATE TABLE token (id INT NOT NULL, token VARCHAR(200) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE token_translation ADD CONSTRAINT fk_985817f541dee7b9 FOREIGN KEY (token_id) REFERENCES token (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE media ADD app_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT fk_6a2ca10c7987212d FOREIGN KEY (app_id) REFERENCES app (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_6a2ca10c7987212d ON media (app_id)');
        $this->addSql('ALTER TABLE navigation ADD app_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE navigation ADD CONSTRAINT fk_493ac53f7987212d FOREIGN KEY (app_id) REFERENCES app (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_493ac53f7987212d ON navigation (app_id)');
        $this->addSql('ALTER TABLE text RENAME COLUMN collapsible TO collapsable');
        $this->addSql('ALTER TABLE section ADD app_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE section ADD CONSTRAINT fk_2d737aef7987212d FOREIGN KEY (app_id) REFERENCES app (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_2d737aef7987212d ON section (app_id)');
        $this->addSql('ALTER TABLE media_folder ADD app_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE media_folder ADD CONSTRAINT fk_50db93137987212d FOREIGN KEY (app_id) REFERENCES app (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_50db93137987212d ON media_folder (app_id)');
        $this->addSql('ALTER TABLE request ALTER checklist TYPE JSON');
        $this->addSql('ALTER TABLE request ALTER checklist DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN request.checklist IS \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE mapping ADD app_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE mapping ADD cms BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE mapping ADD CONSTRAINT fk_49e62c8a7987212d FOREIGN KEY (app_id) REFERENCES app (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_49e62c8a7987212d ON mapping (app_id)');
    }
}
