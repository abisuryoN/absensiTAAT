<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute harus diterima.',
    'accepted_if'          => ':attribute harus diterima ketika :other adalah :value.',
    'active_url'           => ':attribute bukan URL yang valid.',
    'after'                => ':attribute harus berupa tanggal setelah :date.',
    'after_or_equal'       => ':attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha'                => ':attribute hanya boleh mengandung huruf.',
    'alpha_dash'           => ':attribute hanya boleh mengandung huruf, angka, dan tanda hubung.',
    'alpha_num'            => ':attribute hanya boleh mengandung huruf dan angka.',
    'array'                => ':attribute harus berupa array.',
    'ascii'                => ':attribute hanya boleh mengandung karakter alfanumerik dan simbol satu byte.',
    'before'               => ':attribute harus berupa tanggal sebelum :date.',
    'before_or_equal'      => ':attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between'              => [
        'array'   => ':attribute harus mengandung antara :min sampai :max item.',
        'file'    => ':attribute harus berukuran antara :min sampai :max kilobytes.',
        'numeric' => ':attribute harus antara :min dan :max.',
        'string'  => ':attribute harus antara :min sampai :max karakter.',
    ],
    'boolean'              => ':attribute harus berupa nilai true atau false.',
    'can'                  => ':attribute mengandung nilai yang tidak diizinkan.',
    'confirmed'            => 'Konfirmasi :attribute tidak cocok.',
    'current_password'     => 'Password lama tidak sesuai.',
    'date'                 => ':attribute bukan tanggal yang valid.',
    'date_equals'          => ':attribute harus berupa tanggal yang sama dengan :date.',
    'date_format'          => ':attribute tidak cocok dengan format :format.',
    'decimal'              => ':attribute harus memiliki :decimal angka desimal.',
    'declined'             => ':attribute harus ditolak.',
    'declined_if'          => ':attribute harus ditolak ketika :other adalah :value.',
    'different'            => ':attribute dan :other harus berbeda.',
    'digits'               => ':attribute harus terdiri dari :digits digit.',
    'digits_between'       => ':attribute harus antara :min sampai :max digit.',
    'dimensions'           => ':attribute memiliki dimensi gambar yang tidak valid.',
    'distinct'             => ':attribute memiliki nilai yang duplikat.',
    'doesnt_end_with'      => ':attribute tidak boleh diakhiri dengan salah satu dari: :values.',
    'doesnt_start_with'    => ':attribute tidak boleh diawali dengan salah satu dari: :values.',
    'email'                => ':attribute harus berupa alamat email yang valid.',
    'ends_with'            => ':attribute harus diakhiri dengan salah satu dari: :values.',
    'enum'                 => ':attribute yang dipilih tidak valid.',
    'exists'               => ':attribute yang dipilih tidak valid.',
    'extensions'           => ':attribute harus memiliki ekstensi: :values.',
    'file'                 => ':attribute harus berupa file.',
    'filled'               => ':attribute harus memiliki nilai.',
    'gt'                   => [
        'array'   => ':attribute harus mengandung lebih dari :value item.',
        'file'    => ':attribute harus lebih besar dari :value kilobytes.',
        'numeric' => ':attribute harus lebih besar dari :value.',
        'string'  => ':attribute harus lebih dari :value karakter.',
    ],
    'gte'                  => [
        'array'   => ':attribute harus mengandung :value item atau lebih.',
        'file'    => ':attribute harus lebih besar dari atau sama dengan :value kilobytes.',
        'numeric' => ':attribute harus lebih besar dari atau sama dengan :value.',
        'string'  => ':attribute harus :value karakter atau lebih.',
    ],
    'hex_color'            => ':attribute harus berupa warna heksadesimal yang valid.',
    'image'                => ':attribute harus berupa gambar.',
    'in'                   => ':attribute yang dipilih tidak valid.',
    'in_array'             => ':attribute tidak ada dalam :other.',
    'integer'              => ':attribute harus berupa bilangan bulat.',
    'ip'                   => ':attribute harus berupa alamat IP yang valid.',
    'ipv4'                 => ':attribute harus berupa alamat IPv4 yang valid.',
    'ipv6'                 => ':attribute harus berupa alamat IPv6 yang valid.',
    'json'                 => ':attribute harus berupa string JSON yang valid.',
    'list'                 => ':attribute harus berupa list.',
    'lowercase'            => ':attribute harus berupa huruf kecil.',
    'lt'                   => [
        'array'   => ':attribute harus mengandung kurang dari :value item.',
        'file'    => ':attribute harus lebih kecil dari :value kilobytes.',
        'numeric' => ':attribute harus lebih kecil dari :value.',
        'string'  => ':attribute harus kurang dari :value karakter.',
    ],
    'lte'                  => [
        'array'   => ':attribute tidak boleh mengandung lebih dari :value item.',
        'file'    => ':attribute harus lebih kecil dari atau sama dengan :value kilobytes.',
        'numeric' => ':attribute harus lebih kecil dari atau sama dengan :value.',
        'string'  => ':attribute harus :value karakter atau kurang.',
    ],
    'mac_address'          => ':attribute harus berupa alamat MAC yang valid.',
    'max'                  => [
        'array'   => ':attribute tidak boleh mengandung lebih dari :max item.',
        'file'    => ':attribute tidak boleh lebih besar dari :max kilobytes.',
        'numeric' => ':attribute tidak boleh lebih besar dari :max.',
        'string'  => ':attribute tidak boleh lebih dari :max karakter.',
    ],
    'max_digits'           => ':attribute tidak boleh memiliki lebih dari :max digit.',
    'mimes'                => ':attribute harus berupa file bertipe: :values.',
    'mimetypes'            => ':attribute harus berupa file bertipe: :values.',
    'min'                  => [
        'array'   => ':attribute harus mengandung minimal :min item.',
        'file'    => ':attribute harus minimal :min kilobytes.',
        'numeric' => ':attribute minimal :min.',
        'string'  => ':attribute minimal :min karakter.',
    ],
    'min_digits'           => ':attribute harus memiliki minimal :min digit.',
    'missing'              => ':attribute harus tidak ada.',
    'missing_if'           => ':attribute harus tidak ada ketika :other adalah :value.',
    'missing_unless'       => ':attribute harus tidak ada kecuali :other adalah :value.',
    'missing_with'         => ':attribute harus tidak ada ketika :values ada.',
    'missing_with_all'     => ':attribute harus tidak ada ketika :values ada.',
    'multiple_of'          => ':attribute harus merupakan kelipatan dari :value.',
    'not_in'               => ':attribute yang dipilih tidak valid.',
    'not_regex'            => 'Format :attribute tidak valid.',
    'numeric'              => ':attribute harus berupa angka.',
    'password'             => [
        'letters'       => ':attribute harus mengandung setidaknya satu huruf.',
        'mixed'         => ':attribute harus mengandung setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers'       => ':attribute harus mengandung setidaknya satu angka.',
        'symbols'       => ':attribute harus mengandung setidaknya satu simbol.',
        'uncompromised' => ':attribute yang diberikan pernah muncul dalam kebocoran data. Silakan pilih :attribute yang berbeda.',
    ],
    'present'              => ':attribute harus ada.',
    'present_if'           => ':attribute harus ada ketika :other adalah :value.',
    'present_unless'       => ':attribute harus ada kecuali :other adalah :value.',
    'present_with'         => ':attribute harus ada ketika :values ada.',
    'present_with_all'     => ':attribute harus ada ketika semua :values ada.',
    'prohibited'           => ':attribute tidak diizinkan.',
    'prohibited_if'        => ':attribute tidak diizinkan ketika :other adalah :value.',
    'prohibited_unless'    => ':attribute tidak diizinkan kecuali :other adalah salah satu dari :values.',
    'prohibits'            => ':attribute melarang :other untuk hadir.',
    'regex'                => 'Format :attribute tidak valid.',
    'required'             => ':attribute wajib diisi.',
    'required_array_keys'  => ':attribute harus mengandung entri untuk: :values.',
    'required_if'          => ':attribute wajib diisi ketika :other adalah :value.',
    'required_if_accepted' => ':attribute wajib diisi ketika :other diterima.',
    'required_if_declined' => ':attribute wajib diisi ketika :other ditolak.',
    'required_unless'      => ':attribute wajib diisi kecuali :other ada dalam :values.',
    'required_with'        => ':attribute wajib diisi ketika :values ada.',
    'required_with_all'    => ':attribute wajib diisi ketika semua :values ada.',
    'required_without'     => ':attribute wajib diisi ketika :values tidak ada.',
    'required_without_all' => ':attribute wajib diisi ketika semua :values tidak ada.',
    'same'                 => ':attribute dan :other harus sama.',
    'size'                 => [
        'array'   => ':attribute harus mengandung :size item.',
        'file'    => ':attribute harus berukuran :size kilobytes.',
        'numeric' => ':attribute harus berukuran :size.',
        'string'  => ':attribute harus berisi :size karakter.',
    ],
    'starts_with'          => ':attribute harus diawali dengan salah satu dari: :values.',
    'string'               => ':attribute harus berupa string.',
    'timezone'             => ':attribute harus merupakan zona waktu yang valid.',
    'unique'               => ':attribute sudah digunakan.',
    'uploaded'             => ':attribute gagal diunggah.',
    'uppercase'            => ':attribute harus berupa huruf besar.',
    'url'                  => ':attribute harus berupa URL yang valid.',
    'ulid'                 => ':attribute harus berupa ULID yang valid.',
    'uuid'                 => ':attribute harus berupa UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'email'                 => 'email',
        'password'              => 'password',
        'current_password'      => 'password lama',
        'new_password'          => 'password baru',
        'password_confirmation' => 'konfirmasi password',
        'name'                  => 'nama',
        'phone'                 => 'nomor HP',
        'address'               => 'alamat',
        'gender'                => 'jenis kelamin',
        'photo'                 => 'foto',
        'profile_photo'         => 'foto profil',
        'nis'                   => 'NIS',
        'nisn'                  => 'NISN',
        'nip'                   => 'NIP',
        'nik'                   => 'NIK',
        'class_id'              => 'kelas',
        'parent_id'             => 'orang tua',
        'subject_id'            => 'mata pelajaran',
        'date'                  => 'tanggal',
        'token'                 => 'token',
        'year'                  => 'tahun',
        'file'                  => 'file',
    ],

];
