<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('endereco');
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep')->nullable();
            $table->string('telefone');
            $table->date('data_nascimento');
            $table->string('sexo')->nullable();
            $table->string('naturalidade')->nullable();
            $table->string('naturalidade_estado', 2)->nullable();
            $table->string('nacionalidade')->nullable();
            $table->string('rg')->nullable();
            $table->string('orgao_exp')->nullable();
            $table->string('rg_estado', 2)->nullable();
            $table->string('cpf')->unique();
            $table->string('estado_civil')->nullable();
            $table->string('grau_escolaridade')->nullable();
            $table->string('email')->nullable();
            $table->string('empresa')->nullable();
            $table->string('profissao')->nullable();
            $table->string('telefone_profissional')->nullable();
            $table->string('nome_pai')->nullable();
            $table->string('nome_mae');
            $table->string('servico_oferecido')->nullable();
            $table->string('categoria_pretendida')->nullable();
            $table->decimal('valor_pago', 10, 2)->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
