<?php
/**
 * Classe para gerenciar operações de horários
 */

require_once __DIR__ . '/db.php';

class Horarios {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtém os horários de funcionamento de uma barbearia
     * 
     * @param int $id_barbearia ID da barbearia
     * @return array Horários de funcionamento
     */
    public function getHorariosFuncionamento($id_barbearia) {
        return $this->db->fetchAll(
            "SELECT * FROM horarios_funcionamento 
             WHERE id_barbearia = ? 
             ORDER BY dia_semana",
            [$id_barbearia]
        );
    }
    
    /**
     * Obtém o horário de funcionamento de um dia específico
     * 
     * @param int $id_barbearia ID da barbearia
     * @param int $dia_semana Dia da semana (0=Domingo, 1=Segunda, etc.)
     * @return array|false Horário de funcionamento ou false se não encontrado
     */
    public function getHorarioDia($id_barbearia, $dia_semana) {
        return $this->db->fetch(
            "SELECT * FROM horarios_funcionamento 
             WHERE id_barbearia = ? AND dia_semana = ?",
            [$id_barbearia, $dia_semana]
        );
    }
    
    /**
     * Adiciona ou atualiza o horário de funcionamento de um dia
     * 
     * @param int $id_barbearia ID da barbearia
     * @param int $dia_semana Dia da semana (0=Domingo, 1=Segunda, etc.)
     * @param string $hora_abertura Hora de abertura (formato HH:MM)
     * @param string $hora_fechamento Hora de fechamento (formato HH:MM)
     * @param int $intervalo_agendamento Intervalo entre agendamentos em minutos
     * @return bool Sucesso da operação
     */
    public function definirHorarioDia($id_barbearia, $dia_semana, $hora_abertura, $hora_fechamento, $intervalo_agendamento = 30) {
        // Verifica se já existe um horário para este dia
        $horario = $this->getHorarioDia($id_barbearia, $dia_semana);
        
        if ($horario) {
            // Atualiza o horário existente
            return $this->db->update(
                'horarios_funcionamento',
                [
                    'hora_abertura' => $hora_abertura,
                    'hora_fechamento' => $hora_fechamento,
                    'intervalo_agendamento' => $intervalo_agendamento
                ],
                'id_barbearia = ? AND dia_semana = ?',
                [$id_barbearia, $dia_semana]
            ) > 0;
        } else {
            // Adiciona um novo horário
            return $this->db->insert(
                'horarios_funcionamento',
                [
                    'id_barbearia' => $id_barbearia,
                    'dia_semana' => $dia_semana,
                    'hora_abertura' => $hora_abertura,
                    'hora_fechamento' => $hora_fechamento,
                    'intervalo_agendamento' => $intervalo_agendamento
                ]
            ) > 0;
        }
    }
    
    /**
     * Remove o horário de funcionamento de um dia
     * 
     * @param int $id_barbearia ID da barbearia
     * @param int $dia_semana Dia da semana (0=Domingo, 1=Segunda, etc.)
     * @return bool Sucesso da operação
     */
    public function removerHorarioDia($id_barbearia, $dia_semana) {
        return $this->db->delete(
            'horarios_funcionamento',
            'id_barbearia = ? AND dia_semana = ?',
            [$id_barbearia, $dia_semana]
        ) > 0;
    }
    
    /**
     * Obtém os dias fechados de uma barbearia
     * 
     * @param int $id_barbearia ID da barbearia
     * @param string $data_inicio Data de início (formato YYYY-MM-DD)
     * @param string $data_fim Data de fim (formato YYYY-MM-DD)
     * @return array Dias fechados
     */
    public function getDiasFechados($id_barbearia, $data_inicio = null, $data_fim = null) {
        $sql = "SELECT * FROM dias_fechados WHERE id_barbearia = ?";
        $params = [$id_barbearia];
        
        if ($data_inicio) {
            $sql .= " AND data >= ?";
            $params[] = $data_inicio;
        }
        
        if ($data_fim) {
            $sql .= " AND data <= ?";
            $params[] = $data_fim;
        }
        
        $sql .= " ORDER BY data";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Adiciona um dia fechado
     * 
     * @param int $id_barbearia ID da barbearia
     * @param string $data Data (formato YYYY-MM-DD)
     * @param string $motivo Motivo do fechamento
     * @return int|false ID do dia fechado adicionado ou false em caso de erro
     */
    public function adicionarDiaFechado($id_barbearia, $data, $motivo = '') {
        // Verifica se já existe um dia fechado para esta data
        $diaFechado = $this->db->fetch(
            "SELECT id_dia_fechado FROM dias_fechados 
             WHERE id_barbearia = ? AND data = ?",
            [$id_barbearia, $data]
        );
        
        if ($diaFechado) {
            // Atualiza o motivo do dia fechado existente
            $this->db->update(
                'dias_fechados',
                ['motivo' => $motivo],
                'id_dia_fechado = ?',
                [$diaFechado['id_dia_fechado']]
            );
            
            return $diaFechado['id_dia_fechado'];
        } else {
            // Adiciona um novo dia fechado
            return $this->db->insert(
                'dias_fechados',
                [
                    'id_barbearia' => $id_barbearia,
                    'data' => $data,
                    'motivo' => $motivo
                ]
            );
        }
    }
    
    /**
     * Remove um dia fechado
     * 
     * @param int $id_dia_fechado ID do dia fechado
     * @param int $id_barbearia ID da barbearia (para verificação)
     * @return bool Sucesso da operação
     */
    public function removerDiaFechado($id_dia_fechado, $id_barbearia) {
        return $this->db->delete(
            'dias_fechados',
            'id_dia_fechado = ? AND id_barbearia = ?',
            [$id_dia_fechado, $id_barbearia]
        ) > 0;
    }
    
    /**
     * Verifica se uma data é um dia fechado
     * 
     * @param int $id_barbearia ID da barbearia
     * @param string $data Data (formato YYYY-MM-DD)
     * @return bool True se a data for um dia fechado
     */
    public function ehDiaFechado($id_barbearia, $data) {
        $diaFechado = $this->db->fetch(
            "SELECT id_dia_fechado FROM dias_fechados 
             WHERE id_barbearia = ? AND data = ?",
            [$id_barbearia, $data]
        );
        
        return $diaFechado !== false;
    }
    
    /**
     * Verifica se uma barbearia está aberta em um determinado dia da semana
     * 
     * @param int $id_barbearia ID da barbearia
     * @param int $dia_semana Dia da semana (0=Domingo, 1=Segunda, etc.)
     * @return bool True se a barbearia estiver aberta neste dia
     */
    public function estaAbertoNoDia($id_barbearia, $dia_semana) {
        $horario = $this->getHorarioDia($id_barbearia, $dia_semana);
        return $horario !== false;
    }
    
    /**
     * Verifica se um horário está dentro do horário de funcionamento
     * 
     * @param int $id_barbearia ID da barbearia
     * @param int $dia_semana Dia da semana (0=Domingo, 1=Segunda, etc.)
     * @param string $hora Hora (formato HH:MM)
     * @return bool True se o horário estiver dentro do horário de funcionamento
     */
    public function horarioDisponivel($id_barbearia, $dia_semana, $hora) {
        $horario = $this->getHorarioDia($id_barbearia, $dia_semana);
        
        if (!$horario) {
            return false; // Barbearia não abre neste dia
        }
        
        return $hora >= $horario['hora_abertura'] && $hora < $horario['hora_fechamento'];
    }
    
    /**
     * Gera os horários disponíveis para agendamento em um determinado dia
     * 
     * @param int $id_barbearia ID da barbearia
     * @param string $data Data (formato YYYY-MM-DD)
     * @param int $duracao_servico Duração do serviço em minutos
     * @return array Horários disponíveis (formato HH:MM)
     */
    public function gerarHorariosDisponiveis($id_barbearia, $data, $duracao_servico = 30) {
        // Obtém o dia da semana (0=Domingo, 1=Segunda, etc.)
        $dia_semana = date('w', strtotime($data));
        
        // Verifica se a barbearia abre neste dia
        $horario = $this->getHorarioDia($id_barbearia, $dia_semana);
        
        if (!$horario) {
            return []; // Barbearia não abre neste dia
        }
        
        // Verifica se é um dia fechado
        if ($this->ehDiaFechado($id_barbearia, $data)) {
            return []; // É um dia fechado
        }
        
        // Obtém os horários já agendados neste dia
        $agendamentos = $this->db->fetchAll(
            "SELECT hora_inicio, hora_fim 
             FROM agendamentos 
             WHERE id_barbearia = ? AND data_agendamento = ? AND status IN ('agendado', 'confirmado') 
             ORDER BY hora_inicio",
            [$id_barbearia, $data]
        );
        
        // Converte as horas de abertura e fechamento para minutos desde o início do dia
        $abertura_minutos = $this->horaParaMinutos($horario['hora_abertura']);
        $fechamento_minutos = $this->horaParaMinutos($horario['hora_fechamento']);
        
        // Intervalo entre agendamentos
        $intervalo = $horario['intervalo_agendamento'];
        
        // Gera todos os horários possíveis
        $horarios_disponiveis = [];
        $hora_atual = $abertura_minutos;
        
        while ($hora_atual + $duracao_servico <= $fechamento_minutos) {
            $horario_inicio = $this->minutosParaHora($hora_atual);
            $horario_fim = $this->minutosParaHora($hora_atual + $duracao_servico);
            
            // Verifica se o horário não conflita com agendamentos existentes
            $disponivel = true;
            
            foreach ($agendamentos as $agendamento) {
                $agendamento_inicio = $this->horaParaMinutos($agendamento['hora_inicio']);
                $agendamento_fim = $this->horaParaMinutos($agendamento['hora_fim']);
                
                // Verifica se há sobreposição
                if (($hora_atual >= $agendamento_inicio && $hora_atual < $agendamento_fim) ||
                    ($hora_atual + $duracao_servico > $agendamento_inicio && $hora_atual + $duracao_servico <= $agendamento_fim) ||
                    ($hora_atual <= $agendamento_inicio && $hora_atual + $duracao_servico >= $agendamento_fim)) {
                    $disponivel = false;
                    break;
                }
            }
            
            if ($disponivel) {
                $horarios_disponiveis[] = [
                    'hora_inicio' => $horario_inicio,
                    'hora_fim' => $horario_fim
                ];
            }
            
            $hora_atual += $intervalo;
        }
        
        return $horarios_disponiveis;
    }
    
    /**
     * Converte uma hora no formato HH:MM para minutos desde o início do dia
     * 
     * @param string $hora Hora no formato HH:MM
     * @return int Minutos desde o início do dia
     */
    private function horaParaMinutos($hora) {
        list($h, $m) = explode(':', $hora);
        return $h * 60 + $m;
    }
    
    /**
     * Converte minutos desde o início do dia para uma hora no formato HH:MM
     * 
     * @param int $minutos Minutos desde o início do dia
     * @return string Hora no formato HH:MM
     */
    private function minutosParaHora($minutos) {
        $h = floor($minutos / 60);
        $m = $minutos % 60;
        return sprintf('%02d:%02d', $h, $m);
    }
}
?>